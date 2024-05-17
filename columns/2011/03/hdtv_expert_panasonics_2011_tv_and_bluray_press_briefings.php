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
		AND e.entry_id = 4238";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4238 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4238 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4238";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-panasonics-2011-tv-and-bluray-press-briefings.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4238";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV Expert - Panasonic&rsquo;s 2011 TV and Blu-ray Press Briefings" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV Expert - Panasonic&rsquo;s 2011 TV and Blu-ray Press Briefings" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV Expert - Panasonic&rsquo;s 2011 TV and Blu-ray Press Briefings" />
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
	<title>HDTV Magazine - HDTV Expert - Panasonic&rsquo;s 2011 TV and Blu-ray Press Briefings</title>
	<meta name="keywords" content="blu ray, viera connect, plasma tvs, ray players, inches inches, plasma, panasonic, tvs, inches, models, lcd, new, players, inch, blu, ray, wifi, player, viera, line, dmp, both, dvd, –, apps" />
	<meta name="description" content="The theme for this year is ‘connectivity’ across the TV and Blu-ray line." />
	<meta name="title" content="HDTV Expert - Panasonic&amp;rsquo;s 2011 TV and Blu-ray Press Briefings" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV Expert - Panasonic&amp;rsquo;s 2011 TV and Blu-ray Press Briefings" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-panasonics-2011-tv-and-bluray-press-briefings.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The theme for this year is ‘connectivity’ across the TV and Blu-ray line." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4238', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-panasonics-2011-tv-and-bluray-press-briefings.php">HDTV Expert - Panasonic&rsquo;s 2011 TV and Blu-ray Press Briefings</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Pete Putman</b> on <b>March  7, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=366&category=Blu-ray">Blu-ray</a></b>
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
          <p>Last Tuesday and Wednesday, Panasonic held press briefings on its 2011 TV and accessory product line at the House of Glass on 25<sup>th</sup> Street in New York City. Good choice of venue, considering all of the plasma and LCD TVs that were set up for inspection in front of enormous floor-to-ceiling windows.</p>
<p> </p>
<p>As usual, plasma still rules the roost at Panasonic, although LCD technology continues to make inroads. This year, you’ll find 19 new models of plasma TVs and a few new glass cut sizes, such as 55 inches (replaces the 54-inch size) and 60 inches (goodbye, 58 inches).</p>
<p> </p>
<p>The line breaks down into three categories (and I’m using Panasonic’s descriptions here) – twelve Full HD (1080p) 3D plasma TVs, four 1080p FHD plasma sets, and three 720p plasma TVs. (Yes, there is still a market for 720p plasma.)</p>
<div id="attachment_1092" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1092" href="http://www.hdtvexpert.com/?attachment_id=1092"><img class="size-full wp-image-1092" title="3D Plasma Array MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/3D-Plasma-Array-MR.jpg" alt="" width="600" height="346" /></a><p class="wp-caption-text">As usual, Panasonic's got 3D plasma covered.</p></div>
<p>The fact that almost two-thirds of all new Panasonic plasma TVs are 3D-ready reflects the market’s response to higher-priced 3D TVs in 2010: Consumers just weren’t interested in paying a premium for 3D. Now, you can get into a 3D plasma TV for as little as $1100 (TC-P42ST30), while a 50-inch set will cost you $1,500 (TC-P50ST30).</p>
<p> </p>
<p>The top-of-the-line models carry the VT30 suffix and are being marketed in 65-inch and 55-inch sizes (TC-P65VT30, $4,300 and TC-P55VT30, $2,800). Readers may recall that Panasonic’s first 3D offering a year ago was a 50-inch plasma with two pairs of active shutter glasses for $2,800 through Best Buy, so you can appreciate just how much pricing has changed over time.</p>
<p> </p>
<p>In addition to the pair of VT30 models, there are four GT30 plasma 3D TVs from 50 inches ($1,900) to 65 inches ($3,700), and four ST30 variations that also range from 50 inches ($1,500) to 65 inches ($3,300). In the non-3D 1080p (S30) plasma category, Panasonic has four choices from 42 inches ($800) to 60 inches ($1,900), while the three 720p sets are priced at $600 (TC-P42X3), $700 (TC-P64X3), and $800 (TC-P50X3).</p>
<p> </p>
<p>Many of these sets offer the VIERA Connect feature, which provides a host of connected Internet TV channels and specialized apps. Like Samsung, Panasonic is also hosting a connected apps marketplace and will open its platform and middleware technology to third-party developers and manufacturers.</p>
<div id="attachment_1093" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1093" href="http://www.hdtvexpert.com/?attachment_id=1093"><img class="size-full wp-image-1093" title="VIERA Plasma with Solitaire" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/VIERA-Plasma-with-Solitaire.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">No matter what the technology is, everyone eventually finds a way to goof off with it.</p></div>
<p>Some of the more interesting apps that I saw included wellness and fitness apps from Body Media and ICON, one of which lets you track your weight on TV. (Somehow I think that’s not going to be very popular with couch potatoes.) Of course, Skype is ever-present, as are Twitter and Facebook apps and Hulu Plus. And it goes without saying that Netflix is also on all VIERA Connect TVs.</p>
<p> </p>
<p>Over on the LCD side, Panasonic raised some eyebrows by unveiling two of the smallest 3D TVs I’ve seen to date. The TC-L37DT30 (37 inches, $1,300) and TC-L32DT30 (32 inches, $1,200) both use IPS (In Plane Switching) LCD glass, generally the better choice for TVs as it doesn’t have any off-axis color shift issues. And both TVs have LED backlights, which aren’t too common in this screen size.</p>
<p> </p>
<p>I checked out some 3D content on both panels and it was surprisingly free of crosstalk, a problem that often pops up with LCD 3D TVs due to all of the polarizers in the optical path. Both models have the full VIERA Connect suite and also claim a 240 Hz refresh rate.</p>
<p> </p>
<p>Panasonic also has three E3-series models (32, 37, and 42 inches) which also employ LED backlights and will sell for $700, $800, and $950, respectively. Instead of full VIERA Connect features, these models offer Easy IPTV (Netflix, Amazon, and CinemaNow, plus Napster, Pandora, and Facebook).  Another 42-inch LCD model (TC-L42E30) will ticket at $1,100 and adds easy IPTV plus LED backlighting and 120Hz processing, while the TC-L42D30 is a full 1080p LCD TV with VIERA Connect for $1,150.</p>
<div id="attachment_1099" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1099" href="http://www.hdtvexpert.com/?attachment_id=1099"><img class="size-full wp-image-1099" title="37-inch and 32-inch LCD TVs MR" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/37-inch-and-32-inch-LCD-TVs-MR.jpg" alt="" width="600" height="387" /></a><p class="wp-caption-text">Who knew there was a market for 32-inch 3D TVs? (Is there?)</p></div>
<p>What’s interesting is that Panasonic now has as many 42-inch LCD TVs in their line (3) as they do plasma (3). What does that say about the future of 42 inches as a plasma TV size for Panasonic? Company representatives replied that Samsung and LG also sell plasma, but those companies are known largely as LCD TV brands. In contrast, Panasonic built its rep on top-notch plasma picture quality. Is it a price point play? Could be, as the 42-inch LCD sets have higher MSRPs than the equivalent PDPs. Maybe we’re getting closer to the day where 42-inches will just become an LCD size.</p>
<p> </p>
<p>Over in the Blu-ray department, Panasonic has four new models, one of which left me scratching my head. To set things up here, I should mention that Blu-ray player prices have taken precipitous drops in 2010, and that has resulted in an upwards spike in BD player sales. But I would venture – and so far, anecdotal evidence supports me – that consumers are buying Blu-ray players mostly for the connectivity features (spelled N-E-T-F-L-I-X).</p>
<p> </p>
<p>Right now, you can buy several Blu-ray players now for less than $100, and more than one analyst firm predicts we’ll have $40 and $50 BD players by the end of 2011. Not surprisingly, the price premium assigned to 3D BD players has largely evaporated; I picked up a Samsung BDP-C6900 last fall for $244 and you can find them on line for about $170 now.</p>
<p> </p>
<p>The ‘connectivity thing’ is clearly driving a majority of BD player sales. So it was a puzzler to see Panasonic’s new DMP-BD75 in the lineup, as this $99 2D player has no provision for WiFi connectivity; only a conventional RJ-45 Ethernet jack. Bad choice! Consumers don’t want to hard-wire Blu-ray players; they want to use a WiFi connection. But the DMP-BD75 doesn’t even have a WiFi dongle option. This product could be gone from the line as fast as it appeared.</p>
<div id="attachment_1095" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1095" href="http://www.hdtvexpert.com/?attachment_id=1095"><img class="size-full wp-image-1095" title="Blu-ray Lineup" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Blu-ray-Lineup.jpg" alt="" width="600" height="448" /></a><p class="wp-caption-text">Three of the four new Blu-ray players are 3D models.</p></div>
<p>The other three players make a lot more sense. The DMP-BD310 ($399) is the blue-chip model and comes with VIERA Cast and 2D to 3D conversion, plus built-in WiFi connectivity and dual HDMI outputs. Skype is also included, bringing conference calling and an answering machine to your TV. (What WILL they think of next?)</p>
<p> </p>
<p>Stepping down, the DMP-BD210 is ticketed at $299 and has the same features, but only one HDMI output. Both models have touch-free drawer operation – simply wave your hand along the top cover and the disc drawer opens and closes automatically. (Kids are going to have a lot of fun with that!) The DMP-BD110 lops another few bucks off the price, but doesn’t have built-in WiFi or the ‘magic door’ option. A WiFi dongle is available as an option.</p>
<p> </p>
<p>I should mention that WiFi setup and network configuration on all three 3D models is a quantum leap from 2010s models, which practically required you to have Microsoft network certification to complete the process. Now, it’s as easy as setting up a Cisco/Linksys Wireless-N router, which is to say that the BD player basically does all the work. ‘Bout time!</p>
<p> </p>
<p>Panasonic also has a new portable Blu-ray player (DMP-BD200), a portable DVD player (DVD-LS92 -really? Who uses those anymore?), and believe it or not, two new DVD players. One has progressive scan, while the other is upconverting.</p>
<p> </p>
<p>Given that progressive scan DVD players are selling for about $35 these days and upconverting models are around $50, you have to wonder why Panasonic even wants to play in that space anymore. I say, ditch the red laser format and just go blue – the players are certainly cheap enough…</p>
<div id="attachment_1096" class="wp-caption aligncenter" style="width: 610px"><a rel="attachment wp-att-1096" href="http://www.hdtvexpert.com/?attachment_id=1096"><img class="size-full wp-image-1096" title="Grant Clauser and Soundbar" src="http://www.hdtvexpert.com/wp-content/uploads/2011/03/Grant-Clauser-and-Soundbar.jpg" alt="" width="600" height="801" /></a><p class="wp-caption-text">CE Pro's editor Grant Clauser is suitably impresed with the new soundbar.</p></div>
<p>I also saw a few demonstrations of new soundbar technologies and home-theater-in-a-box (HTiB) products, three of which are built around Blu-ray playback and two around DVD playback. The most interesting product was the SC-HTB520 soundbar, which is packaged with a separate wireless subwoofer and sells for $400.</p>
<p> </p>
<p>In the demos I sat through, this soundbar did a surprisingly good job creating a virtual surround sound field and would be of interest for folks who don’t have the space or inclination to set up six different speakers. I could see this soundbar installed with lots of family room TVs (like my 42-inch Panasonic plasma) to add a little spatial separation for prime time TVs shows and sports broadcasts.</p>
                  &#13;
</div>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Pete Putman</b>, <b>March  7, 2011 10:54 AM</b>
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
			<?=getComments(4238)?>
			<div class="dottedline"></div>

			<? if (10 != 7) echo getBoxMoreFromAuthor('Pete Putman', 4238)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2011/03/hdtv-expert-panasonics-2011-tv-and-bluray-press-briefings.php" type="text/javascript" charset="utf-8"></script>
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