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
		AND e.entry_id = 1266";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1266 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1266 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 1266";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 1266";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Q4\'07 Worldwide LCD TV Shipments Surpass CRTs" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Q4\'07 Worldwide LCD TV Shipments Surpass CRTs" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Q4\'07 Worldwide LCD TV Shipments Surpass CRTs" />
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
	<title>HDTV Magazine - Q4'07 Worldwide LCD TV Shipments Surpass CRTs</title>
	<meta name="keywords" content="north america, screen sizes, revenue share, unit basis, revenue basis, share, lcd, shipments, revenue, growth, brand, america, pdp, samsung, unit, total, basis, sony, north, units, market, displaysearch, crt, top, screen" />
	<meta name="description" content="AUSTIN, TEXAS, February 19, 2008-&lt;/b&gt;DisplaySearch, the worldwide leader in display market research and consulting, reported in its latest &lt;i&gt;Quarterly Global TV Shipment and Forecast Report&lt;/i&gt; that global TV shipments grew 21% Q/Q and 5% Y/Y to 60.8 million units, which brought 2007 total shipments to almost 200 million units worldwide. For the full year in 2007, TV revenues exceeded $100 billion for the first time, with Q4'07 revenues climbing 10% Y/Y and 26% Q/Q to a record $32.9 billion.  &lt;p&gt;Also of note, DisplaySearch reported that LCD TV shipments worldwide overtook CRT TV shipments... " />
	<meta name="title" content="Q4'07 Worldwide LCD TV Shipments Surpass CRTs" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Q4'07 Worldwide LCD TV Shipments Surpass CRTs" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="AUSTIN, TEXAS, February 19, 2008-&lt;/b&gt;DisplaySearch, the worldwide leader in display market research and consulting, reported in its latest &lt;i&gt;Quarterly Global TV Shipment and Forecast Report&lt;/i&gt; that global TV shipments grew 21% Q/Q and 5% Y/Y to 60.8 million units, which brought 2007 total shipments to almost 200 million units worldwide. For the full year in 2007, TV revenues exceeded $100 billion for the first time, with Q4'07 revenues climbing 10% Y/Y and 26% Q/Q to a record $32.9 billion.  &lt;p&gt;Also of note, DisplaySearch reported that LCD TV shipments worldwide overtook CRT TV shipments... " />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=1266', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php">Q4'07 Worldwide LCD TV Shipments Surpass CRTs</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>February 19, 2008</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=267&category=Marketplace">Marketplace</a></b>
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
				<p></p>
<p><b>AUSTIN, TEXAS, February 19, 2008-</b>DisplaySearch, the worldwide leader in display market research and consulting, reported in its latest <i>Quarterly Global TV Shipment and Forecast Report</i> that global TV shipments grew 21% Q/Q and 5% Y/Y to 60.8 million units, which brought 2007 total shipments to almost 200 million units worldwide. For the full year in 2007, TV revenues exceeded $100 billion for the first time, with Q4'07 revenues climbing 10% Y/Y and 26% Q/Q to a record $32.9 billion. 
<p>Also of note, DisplaySearch reported that LCD TV shipments worldwide overtook CRT TV shipments for the first time, after rising 56% Y/Y to a record of more than 28.5 million units or 47% of the world TV market. The strong LCD TV share gains can be attributed to 
<ul>
<li><b>Share gains in all regions-</b>LCD unit share improved in every region worldwide, including Europe, which had the strongest growth of the quarter. LCD penetration was highest in developed regions, reaching 86% in Japan, 84% in Western Europe and 78% in North America. But the strongest unit growth for LCD was in developing regions, such as Latin America, Asia Pacific, and Middle East &amp; Africa, which combined rose 106% Y/Y, where penetration is low and the opportunity is substantial. 
<li><b>Natural replacement for CRT-</b>LCD is the only other technology that extends down in screen size to less than 20", which makes it a natural replacement to CRT TVs, as consumers upgrade and CRT TV tube capacity shrinks. Current plasma (PDP) TV technology extends down to 32", but the CRT market is largely below this size, and many regions of the world have limited acceptance of 40"+ screen sizes. As shown in Figure 1, CRT has fallen from 77% of global TV shipments in Q1'06 to 46% in Q4'07, even with LCD prices at a 224% ASP premium for 32" and smaller screen sizes.</li></ul>
<p><b></b>&nbsp; <p><b>Figure 1: Worldwide CRT vs. LCD TV Unit Share</b> 
<p><img style="margin: 0px 0px 5px 5px" height="305" alt="clip_image002" src="http://www.hdtvmagazine.com/images/test/DisplaySearchReportsQ407WorldwideLCDTVSh_ADC1/clip_image002.gif" width="431" border="0"><b></b> 
<ul>
<li><b>Share gains against RPTV and PDP at 40"+-</b>Despite the natural replacement of the CRT TV market, LCD has also made strong share gains against plasma and RPTV technologies with new larger LCD panel fabs optimized to produce larger screen sizes more cost effectively. LCD share of 40"+ TV's has grown from 44% to 65% Y/Y on a unit basis while PDP TV has fallen from 40% to 31% and RPTV is down from 16% to 3%.<b></b></li></ul>
<h2>Results by Technology</h2>
<p><b>LCD TV</b> shipments rose 41% Q/Q and 56% Y/Y to 28.5M units in Q4'07, taking a 47% share of total TV shipments during the quarter, surpassing the 46% share for CRT TV. This brings the 2007 total LCD TV shipments to 79.3M units, a 73% increase from 2006. On a revenue basis, LCD TV grew 34% Y/Y and 31% Q/Q to $22.8B-accumulating almost $68B total in 2007, a 40% boost Y/Y. 
<ul>
<li>LCD led at 15-19", 22-24", 30-34", 35-39", 40-44" and 45-49" screen sizes as the average LCD TV screen size climbed above 32" for the first time in Q4'07. The 40"+<b> size share of the </b>LCD market expanded from 17% to 25% Y/Y on a unit basis and 33% to 44% on a revenue basis. 1080p resolutions also enjoyed strong growth, rising 71% Q/Q and 286% Y/Y to climb to 17% of all LCD TV shipments and 57% of 40"+ units since overtaking HD and lower resolutions in Q3'07. 
<li>Western Europe regained the share lead as the top region for LCD TV shipments, rising from 28% to 32%, overtaking North America which fell to 31% from 33%. 
<li>On a brand share basis, Sony overtook Samsung for the #1 revenue share in LCD TV at 19.5%, the first time since Q1'07 at #1, but Samsung remained #1 on a unit basis. Sony had the strongest Q/Q revenue growth of the top 5 and outpaced total LCD Q/Q revenue growth 2:1 as shown in Table 1. Sony also led in North America and Latin America on a revenue basis, while Samsung was the top brand in European regions as well as Asia Pacific and Middle East &amp; Africa. Sharp led in Japan while Hisense was #1 in China.</li></ul>
<p><b>Table 1: LCD Brand Revenue Share and Growth</b> 
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Sony</td>
<td class="grid" nowrap>
15.9%</td>
<td class="grid" nowrap>
19.5%</td>
<td class="grid" nowrap>
61%</td>
<td class="grid" nowrap>
41%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
18.7%</td>
<td class="grid" nowrap>
19.3%</td>
<td class="grid" nowrap>
35%</td>
<td class="grid" nowrap>
67%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
Philips</td>
<td class="grid" nowrap>
9.7%</td>
<td class="grid" nowrap>
10.1%</td>
<td class="grid" nowrap>
37%</td>
<td class="grid" nowrap>
23%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Sharp</td>
<td class="grid" nowrap>
12.5%</td>
<td class="grid" nowrap>
10.1%</td>
<td class="grid" nowrap>
6%</td>
<td class="grid" nowrap>
21%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
7.8%</td>
<td class="grid" nowrap>
7.7%</td>
<td class="grid" nowrap>
30%</td>
<td class="grid" nowrap>
54%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
35.4%</td>
<td class="grid" nowrap>
33.3%</td>
<td class="grid" nowrap>
24%</td>
<td class="grid" nowrap>
20%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>31%</b></td>
<td class="grid" nowrap>
<b>34%</b></td></tr></tbody></table>
<p>&nbsp; <p><b>PDP TV</b> shipments were up a more modest 29% Y/Y compared to LCD TV, but exhibited the strongest Q/Q growth of any technology at 43% to 4M units in Q4'07. This brings 2007 total shipments to 11.3M units, 22% higher than 2006. On a revenue basis, PDP TV growth was not as robust with 28% Q/Q growth but a 3% Y/Y decline in Q4'07 to $4.8B. 
<ul>
<li>The biggest area of growth for PDP TV has been at smaller screen sizes with the &lt;42" market rising from 8% to 13% Q/Q of plasma shipments and both 32" and 37" volume up over 90% as LCD price reductions slow at 32" and 37" on supply constraints, boosting interest in PDP TV at these sizes. 1080p share of PDP TV shipments rose from less than 1% during Q1'07 to more than 12% during Q4'07, helping to slow share gains by LCD at competing screen sizes. PDP TV held a significant share lead at 50-54", but LCD has started eating into that lead. 
<li>North America continued to be the top region for PDP shipments, but lost share to Europe, mostly at 40-44" screen sizes but also at 55"+. 
<li>By brand on a revenue basis, as shown in Table 2, Panasonic enjoyed healthy share growth from 33% in Q3'07 to 40% in Q4'07 with more than twice the Q/Q revenue gains than #2 Samsung, which had the best Y/Y growth as Panasonic had an extremely strong Q4 a year ago on large price drops. Panasonic topped PDP TV shipments in Japan, North America, Western and Eastern Europe and China while #3 LGE led in Asia Pacific, Latin America, and Middle East &amp; Africa.<b></b></li></ul>
<p><b></b>
<p><b></b>
<p><b></b>
<p><b></b>
<p><b></b>
<p><b>Table 2: PDP TV Brand Revenue Share and Growth</b> 
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Panasonic</td>
<td class="grid" nowrap>
33.0%</td>
<td class="grid" nowrap>
39.6%</td>
<td class="grid" nowrap>
53%</td>
<td class="grid" nowrap>
16%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
21.7%</td>
<td class="grid" nowrap>
20.3%</td>
<td class="grid" nowrap>
20%</td>
<td class="grid" nowrap>
38%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
16.1%</td>
<td class="grid" nowrap>
15.0%</td>
<td class="grid" nowrap>
19%</td>
<td class="grid" nowrap>
-12%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Hitachi</td>
<td class="grid" nowrap>
7.8%</td>
<td class="grid" nowrap>
6.6%</td>
<td class="grid" nowrap>
9%</td>
<td class="grid" nowrap>
-18%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
Pioneer</td>
<td class="grid" nowrap>
7.5%</td>
<td class="grid" nowrap>
6.3%</td>
<td class="grid" nowrap>
8%</td>
<td class="grid" nowrap>
-27%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
13.8%</td>
<td class="grid" nowrap>
12.2%</td>
<td class="grid" nowrap>
13%</td>
<td class="grid" nowrap>
-42%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>28%</b></td>
<td class="grid" nowrap>
<b>-3%</b></td></tr></tbody></table>
<p><b>Microdisplay (MD) RPTV </b>shipments started out the year down, and the decline accelerated throughout the year with Q4'07 falling 60% Y/Y and 6% Q/Q to 348K units, given a 2007 year end total of 1.6M units. MD RPTVs suffered from stiff competition in North America from both LCD and PDP TVs, as well as a lack of regional diversification as 92% of worldwide shipments were made in North America during 2007. </p>
<p>Because of substantial price advantages over PDP and LCD TVs, MD RPTV continues to lead at 55-59" and 60"+ screen sizes. The 1080p share of MD RPTV shipments grew from 74% to 84% Q/Q, with DLP accounting for a dominant share of the RPTV market at 60%, up from 42% a year earlier. 
<p>Samsung remained at the top of MD RPTV revenue share rankings in Q4'07 worldwide at 33.7% with Mitsubishi overtaking Sony for #2 at 30.6%, as Sony announced their exit from the category in Q1'08. 
<h2>Results by TV Brand</h2>
<p><b>Samsung</b> led on a unit basis for the sixth consecutive quarter, picking up a point of share to 15% on the second strongest quarterly growth among the top five and the greatest Y/Y growth, as shown in Table 3. Samsung also had the top revenue brand share for the eighth straight quarter, even stronger than their unit share due to a higher blended average price, rising to 18.6% in Q4'07. Samsung's broad support of many technologies allows them to reach a wide addressable market. Samsung was #2 in LCD, PDP and CRT TVs and was #1 in MD RPTV. Samsung also led in Europe, the strongest growing region for the quarter, as well as in Asia Pacific and Middle East &amp; Africa. 
<p><b>Sony</b> was #2 in TV revenues, rising from 11.7% to 14.4% in Q4'07, doing so with a stronger focus on LCD TV than any other top five brand with 94% of its units shipped by that technology. Sony had the strongest Q/Q TV revenue growth among the top five, with the biggest revenue gains coming from Western Europe. Sony rose to #3 on a unit basis, rising to 8% unit share and overtaking Philips. Sony was also the category leader in LCD TV on a revenue basis and rose to #1 on a unit basis in North America for the first time. 
<p><b>LGE</b> was the #3 brand in TV revenues at 9.4%, unchanged from Q3'07, and improved its #2 unit share position with a half point rise to 11.7%. LGE is a strong competitor in developed regions and was the unit leader in the developing markets of Latin America and Middle East &amp; Africa, while ranking #2 behind Samsung in Asia Pacific. LGE had the top CRT TV unit and revenue share worldwide, still an important category in developing markets. 
<p><b>Table 3: Total TV Brand Unit Share and Growth</b><b> </b>
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
13.9%</td>
<td class="grid" nowrap>
15.0%</td>
<td class="grid" nowrap>
30%</td>
<td class="grid" nowrap>
37%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
11.3%</td>
<td class="grid" nowrap>
11.7%</td>
<td class="grid" nowrap>
25%</td>
<td class="grid" nowrap>
32%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
Sony</td>
<td class="grid" nowrap>
6.2%</td>
<td class="grid" nowrap>
8.0%</td>
<td class="grid" nowrap>
56%</td>
<td class="grid" nowrap>
20%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Philips</td>
<td class="grid" nowrap>
7.0%</td>
<td class="grid" nowrap>
7.4%</td>
<td class="grid" nowrap>
28%</td>
<td class="grid" nowrap>
0%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
TCL</td>
<td class="grid" nowrap>
5.7%</td>
<td class="grid" nowrap>
5.9%</td>
<td class="grid" nowrap>
25%</td>
<td class="grid" nowrap>
-24%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
56.1%</td>
<td class="grid" nowrap>
52.0%</td>
<td class="grid" nowrap>
12%</td>
<td class="grid" nowrap>
-2%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>21%</b></td>
<td class="grid" nowrap>
<b>5%</b></td></tr></tbody></table>
<p><strong></strong>&nbsp; <p><b>Table 4: Total TV Brand Revenue Share and Growth</b> 
<table class="type1b" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="grid">
<b>Rank</b></td>
<td class="grid" nowrap>
<b>Brand</b></td>
<td class="grid" nowrap>
<b>Q3'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q4'07<br>Share</b></td>
<td class="grid" nowrap>
<b>Q/Q<br>Growth</b></td>
<td class="grid" nowrap>
<b>Y/Y<br>Growth</b></td></tr>
<tr>
<td class="grid">
1</td>
<td class="grid" nowrap>
Samsung</td>
<td class="grid" nowrap>
18.3%</td>
<td class="grid" nowrap>
18.6%</td>
<td class="grid" nowrap>
29%</td>
<td class="grid" nowrap>
40%</td></tr>
<tr>
<td class="grid">
2</td>
<td class="grid" nowrap>
Sony</td>
<td class="grid" nowrap>
11.7%</td>
<td class="grid" nowrap>
14.4%</td>
<td class="grid" nowrap>
55%</td>
<td class="grid" nowrap>
17%</td></tr>
<tr>
<td class="grid">
3</td>
<td class="grid" nowrap>
LGE</td>
<td class="grid" nowrap>
9.4%</td>
<td class="grid" nowrap>
9.4%</td>
<td class="grid" nowrap>
26%</td>
<td class="grid" nowrap>
25%</td></tr>
<tr>
<td class="grid">
4</td>
<td class="grid" nowrap>
Panasonic</td>
<td class="grid" nowrap>
7.1%</td>
<td class="grid" nowrap>
8.4%</td>
<td class="grid" nowrap>
48%</td>
<td class="grid" nowrap>
13%</td></tr>
<tr>
<td class="grid">
5</td>
<td class="grid" nowrap>
Philips</td>
<td class="grid" nowrap>
8.0%</td>
<td class="grid" nowrap>
8.3%</td>
<td class="grid" nowrap>
31%</td>
<td class="grid" nowrap>
4%</td></tr>
<tr>
<td class="grid">
&nbsp;</td>
<td class="grid" nowrap>
Other</td>
<td class="grid" nowrap>
45.5%</td>
<td class="grid" nowrap>
40.9%</td>
<td class="grid" nowrap>
13%</td>
<td class="grid" nowrap>
-3%</td></tr>
<tr>
<td class="grid">
<b>&nbsp;</b></td>
<td class="grid" nowrap>
<b>Total</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>100.0%</b></td>
<td class="grid" nowrap>
<b>26%</b></td>
<td class="grid" nowrap>
<b>10%</b></td></tr></tbody></table>
<p>&nbsp; <p>Beginning with the Q1'08 report, DisplaySearch's methodology has been improved by providing brand-level ASPs in North America, which has increased revenue and revenue-based market share accuracy. 
<p>See the #1 North America LCD and Plasma TV brands speak at the <b><i>DisplaySearch US FPD Conference</i></b> to be held March 10-13 in San Diego, California. To view the full agenda and register, visit <a href="http://www.displaysearch.com/usfpd2008">www.displaysearch.com/usfpd2008</a>. 
<p>DisplaySearch's TV market intelligence including panel and TV shipments, TV shipments by region by brand by size for nearly 60 brands, rolling 16-quarter forecasts, TV cost/price forecasts and design wins can be found in its <i>Quarterly Global TV Shipment and Forecast Report</i>. For more information on this report, please contact Arie Braun at (512) 687-1505 or arie@displaysearch.com. 
<h2>About DisplaySearch</h2>
<p>DisplaySearch, an NPD Group company, has a core team of 57 employees located in Europe, North America and Asia who produce a valued suite of FPD-related market forecasts, technology assessments, surveys, studies and analyses. The company also organizes influential events worldwide. Headquartered in Austin, Texas, DisplaySearch has regional operations in Chicago, Houston, Kyoto, London, San Diego, San Jose, Seoul, Shenzhen, Taipei and Tokyo, and the company is on the web at <a href="http://www.displaysearch.com">www.displaysearch.com</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>February 19, 2008  8:15 PM</b>
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
			<?=getComments(1266)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 1266)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/02/q407-worldwide-lcd-tv-shipments-surpass-crts.php" type="text/javascript" charset="utf-8"></script>
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