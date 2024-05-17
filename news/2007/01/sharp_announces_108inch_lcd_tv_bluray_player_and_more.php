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
		AND e.entry_id = 511";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 511 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 511 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 511";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/01/sharp-announces-108inch-lcd-tv-bluray-player-and-more.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 511";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Sharp Announces 108-inch LCD TV, Blu-ray Player, and More" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Sharp Announces 108-inch LCD TV, Blu-ray Player, and More" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Sharp Announces 108-inch LCD TV, Blu-ray Player, and More" />
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
	<title>HDTV Magazine - Sharp Announces 108-inch LCD TV, Blu-ray Player, and More</title>
	<meta name="keywords" content="high definition, home theater, blu ray, contrast ratio, picture quality, sharp, available, new, lcd, high, home, models, aquos, definition, msrp, full, room, inch, theater, series, include, features, panel, front, screen" />
	<meta name="description" content="Sharp is once again raising the bar for LCD technology at 2007 CES with one-of-a-kind innovations and increased performance. The world's largest LCD TV, the 108-inch, is headlining the company's wide breadth of TV offerings, which also includes the newest lines of large-screen AQUOS HDTVs to come from the company's 8th generation factory in Kameyama, Japan, featuring enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs than ever before. In addition, Sharp is showcasing..." />
	<meta name="title" content="Sharp Announces 108-inch LCD TV, Blu-ray Player, and More" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Sharp Announces 108-inch LCD TV, Blu-ray Player, and More" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/01/sharp-announces-108inch-lcd-tv-bluray-player-and-more.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Sharp is once again raising the bar for LCD technology at 2007 CES with one-of-a-kind innovations and increased performance. The world's largest LCD TV, the 108-inch, is headlining the company's wide breadth of TV offerings, which also includes the newest lines of large-screen AQUOS HDTVs to come from the company's 8th generation factory in Kameyama, Japan, featuring enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs than ever before. In addition, Sharp is showcasing..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=511', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/01/sharp-announces-108inch-lcd-tv-bluray-player-and-more.php">Sharp Announces 108-inch LCD TV, Blu-ray Player, and More</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  8, 2007</b>
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
				<p class="prtitle">Sharp Showcases High-Definition Display Dominance at CES with World's Largest LCD TV and Groundbreaking New Technologies</p>

<p><I><CENTER>New 8th Generation AQUOS&reg; HD LCD TVs and Unbelievable 108-Inch Model Plus New DLP Front Projectors Demonstrate Sharp's Display Technology Prowess</CENTER></I></p>

<p><b>LAS VEGAS--(BUSINESS WIRE)</b>--Sharp is once again raising the bar for LCD technology at 2007 CES with one-of-a-kind innovations and increased performance. The world's largest LCD TV, the 108-inch, is headlining the company's wide breadth of TV offerings, which also includes the newest lines of large-screen AQUOS HDTVs to come from the company's 8th generation factory in Kameyama, Japan, featuring enhanced contrast ratios, response times that are among the fastest in the industry, and more inputs than ever before. In addition, Sharp is showcasing groundbreaking technologies and applications that reinforce the company's dominance in the market. Sharp also further expands its large-screen offerings with new additions to its award-winning DLP&reg; front projection lineup.</p>

<p>"Sharp makes one-of-a-kind products that change the way we live," said Bob Scaglione, senior vice president and group manager, Product and Marketing Group, Sharp Electronics Corporation. "LCD is the best flat-panel technology, and we are focused on bringing LCD innovations to the next level at 2007 CES, not only with new AQUOS products that further raise the bar for LCD TV performance, but also with groundbreaking concepts that will change our consumers' lifestyles."</p>

<p>Sharp is a worldwide leader in flat-panel LCD TV and has even won an Emmy&reg; award for the technology behind the AQUOS line*. The company has unsurpassed LCD screen manufacturing at its new state-of-the-art 8th generation factory in Kameyama, Japan, where an entire production line focuses solely on the creation of large-screen units, ensuring that Sharp maintains their top position in the marketplace and exhibiting the company's commitment to achieving even larger screen sizes. This manufacturing superiority helps Sharp offer the most comprehensive flat-panel LCD TV selection, with more than 50 models in screen sizes ranging from 13- to 108-inches.</p>

<p>In addition to LCD TV, Sharp is further broadening its display products line with the debut of new high-definition front projectors that feature the award-winning Texas Instruments DLP&trade; technology. "Sharp's display expertise extends beyond the flat-panel TV category, and as a result we're pleased to be launching a number of new products in our line of DLP front projectors here at CES," Scaglione continued. "In addition to a new 1080p flagship product, we also have a number of smaller, portable projectors, for a full range of offerings, so that we can open up the world of projection to many more consumers. Sharp is completely committed to maintaining a leadership position in this segment, as well."</p>

<p>Cementing the company's future presence in tech-savvy households, Sharp is also demonstrating sophisticated, breakthrough technologies that capture the near- and long-term potential of LCD in home, mobile and broadcast environments. Featured technologies at CES include a preview of wireless image-beaming from cameras and cell phones to TVs, as well as the first productizing of modems that transfer two high-definition feeds through one power line in the home. In addition, Sharp will be demonstrating how consumers will be able to access PC content on their TV, bringing the Internet right into the living room. For the broadcast professional, a new LCD monitor with a 1,000,000:1 contrast ratio for a crystal-clear picture will be demonstrated for use in demanding lighting conditions and picture-quality requirements, as well as a four thousand by two thousand resolution model, which doubles today's 1080p resolution. Finally, for mobile use in automobiles, Sharp is showcasing a three-way viewing angle display that lets three or more users watch three different programs that occupy the full screen of a single display simultaneously.</p>

<p>Additionally, Sharp is showcasing an iPod&reg; docking system and a series of TVs specifically intended for gamers. For detailed information on Sharp's new products, please see the individual product announcements and fact sheets.</p>

<p><br />
<h2>108-inch High-Definition LCD TV</h2><br />
Sharp has successfully developed a 108-inch LCD TV, the world's largest, and will exhibit a prototype model. This 108-inch Full HD 1080p LCD TV, which measures 93.9-inches (W) by 52.9-inches (H) in size, features Sharp's Advanced Super View LCD Panel manufactured at Sharp's Kameyama Plant No. 2, the first facility in the world to produce panels from eighth-generation glass substrates. The success of this development means that it is now possible to produce LCD TVs in all sizes, from 13-inches to the super-large-size class.</p>

<p><br />
<h2>AQUOS Widescreen 1080p HDTV Series (models LC-42D92U, LC-46D92U, LC-52D92U and LC-65D93U)</h2><br />
The new widescreen series of Full HD1080p HDTV AQUOS Liquid Crystal Televisions, available in 42-, 46-, 52-, and 65-inch screen sizes, features the top-of-the-line version of Sharp's proprietary Advanced Super View panel, the most advanced LCD panel in the world. This panel technology enables an incredible 15,000:1 Dynamic Contrast Ratio for deep blacks and crisp picture quality; Fine Motion Advanced technology for 120 Hz Frame Rate Conversion; enhanced Quick Shoot video circuitry for faster pixel response time of 4ms; and wide viewing angles of 176 degrees, so users can view the proprietary 5-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds and greens than was previously possible. Additionally, all units in this series include three HDMI&trade; inputs, two HD component terminals, and one DVI-I input, all of which are compatible with 1080p signals from Blu-ray and other new devices, in addition to RS-232C for custom installations. The entire series features Full HD 1080p (1920 x 1080) resolution for an unparalleled high-definition experience. These newly redesigned models are available in a stunning piano black finish with detachable bottom speakers and include a detachable table stand. Also joining these new Full HD 1080p AQUOS LCD TVs is a 65-inch model that shares the same advanced features as the D92U line but offers a varied design of high-gloss piano black finish with fixed bottom-placed speakers. The LC-42D92U will be available in April for a Manufacturer's Suggested Retail Price (MSRP) of $3,499.99 and the LC-46D92U and LC-52D92U and will be available in January for MSRPs of $4,199.99 and $5,299.99, respectively. The LC-65D93U will be available in March for an MSRP of $10,999.99.</p>

<p><br />
<h2>AQUOS&reg; Widescreen 1080p HDTV Series (models LC-52D82U and LC-46D82U)</h2><br />
This new series of Full HD1080p HDTV AQUOS Liquid Crystal Televisions, available in 52- and 46-inch screen sizes, is produced at the new 8th-generation Kameyama plant for high picture quality and specifications. This panel features an incredible 10,000:1 Dynamic Contrast Ratio, for deep blacks and crisp picture quality; Fine Motion Advanced technology for 120 Hz Frame Rate Conversion; enhanced Quick Shoot video circuitry for faster pixel response time of 4ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. The D82 models also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, both models include three HDMI&trade; inputs as well as two HD component terminals, all of which are compatible with 1080p signals from Blu-ray and other new devices. These models feature Full HD 1080p (1920 x 1080) resolution for an unparalleled high-definition experience, and they are available in a piano black finish. Both models will be available in March; the LC-52D82U will have an MSRP of $4,799.99 and the LC-46D82U of $3,699.99.</p>

<p><br />
<h2>AQUOS Widescreen 720p HDTV Series (models LC-52D43U, LC-46D43U, LC-42D43U, LC-37D43U, LC-32D43U and LC-26D43U)</h2><br />
The new widescreen 52-, 46-, 42-, 37-, 32- and 26-inch HDTV AQUOS D43U Liquid Crystal Televisions further bolster Sharp's unmatched selection of sophisticated designs and its superior-performing LCD TVs. Sharp's proprietary Advanced Super View LCD panel enables a Dynamic Contrast Ratio of 6000:1, enhanced Quick Shoot video circuitry for fast pixel response time (6ms) and wide viewing angles (176 degrees), so users can view the television from almost anywhere in the room. The newly-designed series features an elegant piano black finish with fixed bottom speakers and a detachable table stand for wall-mounting flexibility. With 1366 x 768 resolution for true 16:9 aspect ratio, and built-in ATSC/QAM/NTSC tuners, consumers can enjoy the latest HDTV programming, and the addition of a PC input makes the panel multifunctional for any room. Models LC-37D43U and LC-32D43U are available now for MSRPs of $1,699.99 and $1,399.99, respectively. The LC-26D43U will be available in February for an MSRP of $1,099.99. The LC-42D43U will be available in May, pricing has not yet been determined. The LC-46D43U will have an MSRP of $2,699.99 and will be available in March. The LC-52D43U will be available in June and will have an MSRP of $3,999.99.</p>

<p><br />
<h2>AQUOS HDTV Game Players Series (models LC-37GP1U, LC-32GP1U)</h2><br />
Sharp has introduced a new Full HD 1080p AQUOS series crafted specifically for video game enthusiasts, the GP1U series, available in 32- and 37-inch screen sizes. These new models include special features that enhance the game-playing experience, including a "game mode" which optimizes the picture quality for game-playing, and a custom-designed remote control that allows the user to quickly "jump" into the game mode, and access the side-placed terminals for easy connections to video games. The game mode provides a newly developed "Vyper Drive" feature, which reduces lag time between the game console and the TV to be virtually imperceptible. The 32-inch panel boasts an incredible 10,000:1 Dynamic Contrast Ratio, and the 37-inch an 8500:1, for deep blacks and crisp picture quality; enhanced Fine Motion video circuitry for faster pixel response time of 6ms; and wide viewing angles of 176 degrees, so users can view the television from virtually anywhere in the room. They also include Sharp's proprietary 4-wavelength backlight system that provides a wider color spectrum to achieve deeper, more vivid reds than was previously possible. Additionally, both of the GP1U models include three HDMI&trade; inputs (with one on the side) as well as two HD component terminals (one on the side), all of which are compatible with 1080p signals from the latest video game devices. Both units in the series feature Full HD 1080p (1920 x 1080) resolution and 10Wx2 audio for an unparalleled high-definition viewing and listening experience. These models are available in a piano black finish with detachable bottom speakers and include a detachable table stand. The LC-32GP1U and LC-37GP1U will be available in March for MSRPs of $1,699.99 and $1,999.99 respectively.</p>

<p><br />
<h2>Flagship "Full HD" 1080p DLP&reg; Home Theater Front Projector (model XV-Z20000):</h2><br />
Sharp brings home theater to the forefront with the XV-Z20000, utilizing the latest 0.95"single DMD from Texas Instruments. This high-gloss black, groundbreaking "Full HD"1080p projector has a native resolution of 1920 x 1080 for a true 16:9 widescreen movie viewing experience, producing stunning vivid images. The XV-Z20000 transforms any room into a high-tech home theater, using Sharp's CV-IC III Video Scaling Circuitry that up-converts all signals to 1080p. The Z20000 also boasts a native 12000:1 contrast ratio and a brightness spec of 1000 ANSI lumens, delivering one of the best pictures available in consumer home theater projectors today. DVI/HDCP (High Bandwidth Digital Content Protection) and two HDMI&trade; terminals ensure a secure digital connection with all high definition set top boxes. The XV-Z20000 is available now for an MSRP of $11,999.99.</p>

<p><br />
<h2>High Definition DLP&reg; Home Theater Front Projector (model DT-510):</h2><br />
The DT-510 high-definition DLP&reg; front projector is feature-packed, with a stylish gloss-white design that is ideal for a dedicated home theater or any viewing room in the home. Weighing just 8.8 pounds, this portable unit can be moved easily from room to room, for an instant home theater anywhere. Utilizing the DLP&reg; technology from Texas Instruments, and with a resolution of 1280 x 720, the DT-510 produces a 4000:1 contrast ratio and a brightness rating of 1000 ANSI lumens, delivering one of the best pictures available in consumer home theater today. A powered optical iris system instantly changes brightness and contrast settings with the push of a button to allow the greatest flexibility for varying home theater environments. Home theater convenience is further enhanced with easy installation and whisper-quiet operation. A 6 Segment 5 X Speed color wheel achieves flicker-free, high-grade images and accurate color reproduction, resulting in an uninterrupted, detailed picture. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost and an HDMI&trade; interface. The DT-510 will be available in February for an MSRP of $2,499.99.</p>

<p><br />
<h2>High Definition DLP&reg; Home Theater Front Projector (model XV-Z3100):</h2><br />
Sharp's next-generation portable DLP&reg; front projector, the SharpVision XV-Z3100, is a 720p high-definition home entertainment solution that instantly transforms any room into a high-tech home theater. This high-gloss black, widescreen portable projector can be carried throughout the house or to a friend's home to create an instant home theater for watching TV, viewing DVDs or playing computer games on a big screen. The XV-Z3100 features brightness (1000 ANSI Lumens) and a contrast level of 6500:1, superior to those available in current front projectors, so consumers can enjoy excellent picture quality in almost any lighting condition. The low fan noise of 29dB (in economy mode) ensures that the viewer won't miss a minute of the film's dialogue and special effects. Other features include I/P conversion, 3-2 pull down, Color Management System (C.M.S.), 3-step Bright Boost, a 12-volt trigger and an HDMI&trade; interface. The XV-Z3100 will be available in February for an MSRP of $2,699.99.</p>

<p><br />
<h2>Blu-Ray:</h2><br />
Sharp is demonstrating Blu-ray capabilities with a new product that will enter the market in the second quarter of spring 2007. This new Blu-ray player will help consumers realize the powerful next generation of home entertainment enabled by the high-definition Blu-ray format. The user-friendly player features a built-in HDMI&trade; digital AV interface that can be connected to an AQUOS LCD HDTV, allowing consumers to watch a Blu-ray disc in complete digital high-definition with high-quality picture and audio. The player will have an MSRP of $1,199.99.</p>

<p><br />
<h2>i-Elegance Music Systems for iPod&reg;:</h2><br />
Sharp's new i-Elegance Docking stereo systems allow the user to charge and play music directly from any iPod. The lightweight units feature full range bass reflex speakers with built-in side firing subwoofers that illuminate. With a sleek design and rounded edges, the new stereo systems will be available in white (DK-A1 and DK-A10) and black (DK-A1BK and DK-A10BK) and come with a thin-style remote control. Sleek and compact, these Sharp systems (compatible with iPod) fit into small spaces, such as a shelf or side table, and can be carried from room to room. An AM/FM tuner provides radio playback for both models, and in addition, the DK-A10 and DK-A10BK have a front-loading CD slot and are capable of playing MP3 and WMA files from a CD-R/RW disc. Both models also feature Alarm Clock and Sleep Timer functions. The DK-A1 and DK-A1BK will be available in May for an MSRP of $229.99. The DK-A10 and DK-A10BK will be available in April for an MSRP of $329.99.</p>

<p><br />
<h2>Micro Audio Systems (model XL-UH270 and XL-UH250):</h2><br />
The XL-UH270 and XL-UH250 are new micro systems that incorporate digital audio playback into the system from an MP3 player via a USB connection. Consumers can take the music from a personal, portable MP3 player and play it for guests through the Micro System. In addition to MP3 playback, the XL-UH270 is one of Sharp's first systems to offer XM-Ready&reg; service so users can play XM Satellite Radio in any room of the home with activation and monthly subscription. The XL-UH270 features a powerful 230-watt amplifier and the XL-UH250 features a 190-watt amplifier; both units include a 5-tray CD Changer with Play Exchange for continuous CD playback, an AM/FM tuner, and a multicolor fluorescent display, and both support CD, CD-R/RW, MP3, & WMA playback capabilities. The XL-UH270 will be available in March 2007 for an MSRP of $159.99. The XL-UH250 will be available in April for an MSRP of $139.99.</p>

<p>For more information on Sharp's full line of products, contact Sharp Electronics Corporation, Sharp Plaza, Mahwah, N.J. 07430, or call 800-BE-SHARP. For online product information, visit Sharp's virtual press room at sharppressroom.com or sharpusa.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&trade; Microwave Drawers, IMAGER&trade; digital multifunctional systems, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com</p>

<p>*Sharp won a 2004 Technology & Engineering Emmy&reg; for Award for Development of Direct View Liquid Crystal Display Screens. Use of the trademarks and service marks of the National Television Academy, including the mark Emmy&reg;, requires the prior express written permission of the National Television Academy.</p>

<p>AQUOS is a registered trademark of Sharp Corporation</p>

<p>HDMI, the HDMI logo and High Definition Multimedia Interface are trademarks of HDMI Licensing, LLC.</p>

<p>DLP is a trademark of Texas Instruments.</p>

<p>iPod is a trademark of Apple.</p>

<p>XM-Ready is a registered trademark of XM Satellite Radio Inc.</p>

<p>All other trademarks are the property of their respective owners.<br />
Contacts</p>

<p>Stanton Crenshaw Communications<br />
Robin Feldman, 646-502-3504<br />
rfeldman@stantoncrenshaw.com</p>

<p>Sharp Electronics Corporation<br />
Chris Loncto, 201-529-8680<br />
lonctoc@sharpsec.com</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  8, 2007  6:53 AM</b>
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
			<?=getComments(511)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 511)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/01/sharp-announces-108inch-lcd-tv-bluray-player-and-more.php" type="text/javascript" charset="utf-8"></script>
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