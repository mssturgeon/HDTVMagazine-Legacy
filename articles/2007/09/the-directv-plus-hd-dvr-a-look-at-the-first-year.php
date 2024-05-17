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
		AND e.entry_id = 709";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Doug Brott" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 709 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Doug Brott'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Doug Brott" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 709 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 709";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/09/the-directv-plus-hd-dvr-a-look-at-the-first-year.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (1) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 709";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The DIRECTV Plus&reg; HD DVR: A Look at the First Year" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The DIRECTV Plus&reg; HD DVR: A Look at the First Year" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The DIRECTV Plus&reg; HD DVR: A Look at the First Year" />
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
	<title>HDTV Magazine - The DIRECTV Plus&reg; HD DVR: A Look at the First Year</title>
	<meta name="keywords" content="dbstalk user, dbstalk users, high definition, directv plus, earl bonovich, directv, dbstalk, customers, new, user, first, dvr, users, while, software, plus, could, feature, channels, list, guide, recorded, hard, problems, been" />
	<meta name="description" content="Despite its troubled start, the DIRECTV Plus&amp;reg; HD DVR has evolved into a capable high-definition video recorder. In addition, the Plus&amp;reg; HD DVR has been the driving force in the creation of a strong community of enthusiasts taking an unprecedented role in the software release cycle with DIRECTV leading the way. Now it's time to look back at the DIRECTV Plus&amp;reg; HD DVR journey.

January, 2006: DIRECTV's first homegrown Digital Video Recorder (DVR), the R15, has been in use for several months. The HR10-250, a high-definition DVR based on the TiVo software, has a list price of $1,000. The satellite world is ready for an alternative." />
	<meta name="title" content="The DIRECTV Plus&amp;reg; HD DVR: A Look at the First Year" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The DIRECTV Plus&amp;reg; HD DVR: A Look at the First Year" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/09/the-directv-plus-hd-dvr-a-look-at-the-first-year.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Despite its troubled start, the DIRECTV Plus&amp;reg; HD DVR has evolved into a capable high-definition video recorder. In addition, the Plus&amp;reg; HD DVR has been the driving force in the creation of a strong community of enthusiasts taking an unprecedented role in the software release cycle with DIRECTV leading the way. Now it's time to look back at the DIRECTV Plus&amp;reg; HD DVR journey.

January, 2006: DIRECTV's first homegrown Digital Video Recorder (DVR), the R15, has been in use for several months. The HR10-250, a high-definition DVR based on the TiVo software, has a list price of $1,000. The satellite world is ready for an alternative." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Articles Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=709', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/09/the-directv-plus-hd-dvr-a-look-at-the-first-year.php">The DIRECTV Plus&reg; HD DVR: A Look at the First Year</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Doug Brott</b> on <b>September 11, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=10&category=New Products & Equipment">New Products & Equipment</a></b>
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
				<p class="editorial">This article is a collective effort of Doug Brott, Craig Lincoln and Stuart Sweet, and was originally made public on the <a href="http://www.dbstalk.com/">DBSTalk Forums</a>.

<p>Despite its troubled start, the DIRECTV Plus&reg; HD DVR has evolved into a capable high-definition video recorder. In addition, the Plus&reg; HD DVR has been the driving force in the creation of a strong community of enthusiasts taking an unprecedented role in the software release cycle with DIRECTV leading the way. Now it's time to look back at the DIRECTV Plus&reg; HD DVR journey.</p>

<p>January, 2006: DIRECTV's first homegrown Digital Video Recorder (DVR), the R15, has been in use for several months. The HR10-250, a high-definition DVR based on the TiVo software, has a list price of $1,000. The satellite world is ready for an alternative.</p>

<p><img src="/images/articles/hr20-ces.jpg" alt="DIRECTV shows a mockup of its new Plus HD DVR at the Consumer Electronics Show" /><br />
DIRECTV shows a mockup of its new Plus HD DVR at the Consumer Electronics Show.</p>

<p>At DIRECTV's Investor meeting in February, a single slide is displayed that shows the upcoming direction of their user interface. It's a tantalizing taste of things to come.</p>

<p>August 16, 2006: DBSTalk.com scoops the world with its first look at the newest member of the DIRECTV Plus&reg; series, the HR20-700 high-definition DVR. The specs are impressive: 300GB hard drive, MPEG2 and MPEG4 decompression, two satellite inputs, an Ethernet port, and an evolution of the user interface found in the R15.</p>

<p>The feature set is impressive:<ul><li>It can record two programs while you are watching a third previously recorded.</li><li>It allows the customer to set bookmarks in recorded material.</li><li>It allows the native output of TV in the resolution in which it was recorded.</li><li>Setting up programs to record is almost instant.</li><li>Series Links can be created with the press of a single button.</li><li>Rearranging items in the Prioritizer is nearly instant.</li><li>Live TV appears in a small window while you are navigating the menus and guide.</li></ul></p>

<p>At this early stage, the list of what the HR20 does not do is almost as impressive as what it does do, and numerous bugs are found. Updates are quickly requested.</p>

<p>Initially, the HR20 was only available in southern California, so while the rest of the DBSTalk "nation" waits and hopes, those who have received their HR20s form two camps: the lovers and the haters. As luck would have it, Earl Bonovich of DBSTalk.com had cultivated a strong relationship with DIRECTV. This relationship would soon play a crucial role in the rapid improvement of the HR20, but first came the early days and what can best be described as a dark period.</p>

<p><br />
<b>The Early Days</b></p>

<p>Some DBSTalk users experienced little to no problems, while others were constantly experiencing issues. Folks clamored for hundreds of new features. To make matters worse, despite DIRECTV's hard work, each new software release seemed to create new problems for some as often as it resolved old problems for others. For some, even using Fast Forward could lock up their receivers. Southern California customers tended to get updates first, while the rest of the country either hoped the new code would spread their way or dreaded that it might.</p>

<p>Despite the reported issues, demand for the HR20 was still huge. DBSTalk users still jumped at the chance to bring one into their homes, happily posting when one arrived, or posting locations where they could be found. It was clear from the start that DIRECTV could have a huge hit on its hands once the bugs were ironed out. At that dark moment, the user community showed its true stripes...</p>

<p><br />
<b>HR20 Episode IV: A New Hope</b></p>

<p>Out of all this confusion sprang a little bit of hope. Customers began to take an active stand to help out. Earl Bonovich fielded questions, and other DBSTalk users began to offer solutions that worked for them. As the cream began to rise to the top, people like Craig Lincoln (DBSTalk user Milominderbinder2) began to offer suggestions in the form of Tips and Tricks documents. Doug Brott (DBSTalk user brott) volunteered to create a "Wish List" website to track feedback and feature requests, which ranged from Four Tuners to Dual Live Buffers. Other DBSTalk users started a catalog of HR20 bugs. All of this was done for the betterment of the HR20.</p>

<p>As October rolled into November, things started to improve. The HR20 was finally stable enough that customers wanted more than just trick play. HR20 enthusiasts had managed to find workarounds for many of the missing features. The eSATA expansion port was added in one software release that enabled customers to add large external hard drives, thus increasing recording capacity. The 4x Fast Forward and Rewind features were added, allowing customers to move at 90 times normal playback speed. In addition Advance (30 Second Slip) took just one second instead of more than 3 seconds per click.</p>

<p><br />
<b>Santa Claus Comes to Town</b></p>

<p>December marked the beginning of the "Cutting Edge" program at DBSTalk.com. Led by DBSTalk user hasan and others who had no local service through DIRECTV, it was the need for OTA (or over-the-air) reception that drove DIRECTV and DBSTalk.com to cooperate on the very first optional software download for HR20s: version 0x104. Through volunteer DBSTalk users including Earl Bonovich and Tom Robertson, DIRECTV invites volunteers to test new software releases before they are released nationally. These volunteers provide feedback from every market in the U.S. The CE program is truly unique in its scope, level of involvement, and the enthusiasm of its volunteers. The first few CEs, as they've come to be called, sported names such as Santa, Elvis, and Benz.</p>

<p>The Santa CE release unlocked the OTA feature, enabling customers to see local over-the-air channels on the HR20 for the first time. Customer satisfaction improved, but there was still a long way to go. Santa also brought new multimedia capabilities that allowed the HR20 to show pictures or play music from a PC.</p>

<p>The Elvis CE release began work on the Title Search Autorecord, and the Benz CE enabled Autorecord padding, while squelching the Random Screen Saver bug.</p>

<p>February improvements included song information on XM Channels, the 90 Minute Buffer in Standby mode, and the new GameLounge. And on February 17, DIRECTV allowed customers to customize the Guide Button! Now the Guide button could go straight to the Guide!</p>

<p><br />
<b>The Hits Just Keep On Coming!</b></p>

<p>March saw Sound Effects enabled and Guide data cached to disk for quick access after a software update. On March 5, DIRECTV added options to allow customers to turn off Animations, the slow crawl that took place while navigating through the Guide or menus. In addition, My Playlist could now remember how customers preferred to view their listings of recorded programs.</p>

<p>Searches were improved to allow more flexible wording and to address issues with special characters. Dave Galanter (DBSTalk user Capmeister) spearheaded a movement to get Closed Captioning working properly. As a result, DIRECTV put the vast majority of captioning problems to bed; however a few issues remain.</p>

<p>Low VHF Channels and Pay Per View issues were addressed, and Showcases were enabled. The new White GUI came in May and eliminated one of our favorite bugs -- Pinky. Pinky would make normally dark blue backgrounds change to bright pink. Pinky -- may she rest in peace.</p>

<p>Thanks to the efforts of the Cutting Edgers, led by Jim Litz (DBSTalk user litzdog911), Caller ID functionality was fixed in a series of releases which adjusted sensitivity to be "just right." HDMI was used right away, but there were challenges due to the different manufacturers' implementations. The development team worked hard to fix the HDMI problems, aided by Cutting Edgers like Mike Smith (DBSTalk user Radio Enginerd) and others who posted information about their TVs.</p>

<p>In March, DIRECTV even took an unprecedented step of asking CE users to test an unreleased product -- Single Wire Multiswitch (SWM). This revolutionary technology, expected later this year, allows a single cable to be used for each receiver, including the dual-tuner HR20, which would normally require two cables.</p>

<p>Two more Wish List items were made available! Customers could now play a group of programs one after another, and recording conflicts were now noted in the To Do list.</p>

<p><br />
<b>To Infinity and Beyond!</b></p>

<p>June brought Fast Forward Correction. This much-requested feature allows a customer to press play while fast forwarding and causes video playback to resume very close to where expected. In the past the HR20 forced customers to hit the Replay button one or more times to achieve the same results. This manual process had now been automated. In addition, [HD] icons were added to the titles of high-definition shows in both the Search Results and the To Do list.</p>

<p>Some of the other things that have come out of the CE process are:<br />
<ul><li>Customers can tune to a channel by simply entering the channel numbers without pressing Enter.</li><li>Special characters are now ignored in a keyword search.</li><li>ZIP code is now cached so we no longer have to re-input for ACTIVE content.</li><li>The Music & Photos feature expanded to a full-screen interactive interface.</li></ul></p>

<p><br />
DIRECTV On Demand is now in "Cutting Edge" testing, giving HR20 users thousands of new programming options.</p>

<p>In the very near future, activation of the Channels I Receive (CIR) feature should prevent the HR20 from recording blocked channels, and dozens of new HD channels are coming online that can't be recorded with any other DVR.</p>

<p><br />
<B>To Sum it Up</B></p>

<p>The first year with the HR20 has been a wild and exciting ride. The CE program should be a case study in how a company can use its customer base as an integral part of its product development. It's hard to imagine a better result than the ongoing dialogue between DIRECTV and its customers. There's no doubt the best is yet to come for the HR20 as we enjoy DIRECTV On Demand, watch new HD content, and see the few final "squashes" for those last remaining bugs.</p>

<p>We've been proud to be a part of the development of the HR20. We didn't start out wanting to "be the solution"; at first we just wanted a great product. When we realized that we had an opportunity to shape the HR20's future and make it better for everyone, we were glad to jump right in. Here's to you, HR20!</p>

<p><B>Doug Brott<br /><br />
Craig Lincoln<br /><br />
Stuart Sweet</B></p>

<p>Special thanks to DBSTalk users Andy Thome, Drew Fleece and Jim Litz for editing and advice. Thanks to the hundreds of frequent posters at DBSTalk.com for keeping the HR20 community going with helpful ideas and good intentions. We also want to thank Chris Blount, Earl Bonovich, and Tom Robertson for their supervision and inspiration, and of course the DIRECTV HR20 development team for opening up the process to the enthusiastic user community.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Doug Brott</b>, <b>September 11, 2007  7:47 AM</b>
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
			<?=getComments(709)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Doug Brott', 709)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Doug Brott</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/the-directv-plus-hd-dvr-a-look-at-the-first-year.php" type="text/javascript" charset="utf-8"></script>
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