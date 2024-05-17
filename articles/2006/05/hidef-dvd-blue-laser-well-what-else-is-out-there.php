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
		AND e.entry_id = 371";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 371 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Rodolfo La Maestra" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 371 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 371";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2006/05/hidef-dvd-blue-laser-well-what-else-is-out-there.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 371";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Hi-Def DVD? - Blue laser?  Well, what else is out there?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Hi-Def DVD? - Blue laser?  Well, what else is out there?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Hi-Def DVD? - Blue laser?  Well, what else is out there?" />
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
	<title>HDTV Magazine - Hi-Def DVD? - Blue laser?  Well, what else is out there?</title>
	<meta name="keywords" content="def dvd, blu ray, blue laser, well else, evd player, dvd, format, evd, def, market, chinese, back, ces, could, time, blu, ray, product, content, price, unit, technology, years, player, minutes" />
	<meta name="description" content="Although I have been following the Hi-Def DVD development since 1996 when DVD was introduced, it was since January 2002 that I have started to provide details on my reports and articles about the several hi-def DVD formats.  Not only about Blu-ray and HD DVD that are now at market war trying to attract the consumer in the US, but also about the Chinese DVD HD video industry, which has now 4 formats (and we thought 2 were more than we needed)." />
	<meta name="title" content="Hi-Def DVD? - Blue laser?  Well, what else is out there?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Hi-Def DVD? - Blue laser?  Well, what else is out there?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2006/05/hidef-dvd-blue-laser-well-what-else-is-out-there.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Although I have been following the Hi-Def DVD development since 1996 when DVD was introduced, it was since January 2002 that I have started to provide details on my reports and articles about the several hi-def DVD formats.  Not only about Blu-ray and HD DVD that are now at market war trying to attract the consumer in the US, but also about the Chinese DVD HD video industry, which has now 4 formats (and we thought 2 were more than we needed)." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=371', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2006/05/hidef-dvd-blue-laser-well-what-else-is-out-there.php">Hi-Def DVD? - Blue laser?  Well, what else is out there?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Rodolfo La Maestra</b> on <b>May  8, 2006</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=11&category=Technology">Technology</a></b>
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
				<p><b><center>Hi-Def DVD? - Blue laser?  Well, what else is out there?</p>

<p>Part I</b></center></p>

<p>Although I have been following the Hi-Def DVD development since 1996 when DVD was introduced, it was since January 2002 that I have started to provide details on my reports and articles about the several hi-def DVD formats.  Not only about Blu-ray and HD DVD that are now at market war trying to attract the consumer in the US, but also about the Chinese DVD HD video industry, which has now 4 formats (and we thought 2 were more than we needed).  </p>

<p>I will cover the subject in several articles, including a meeting with the companies behind the new FVD HD format from Taiwan (full details in my 2006 report). </p>

<p>To start with, picture yourself for a few minutes back into an era of continuous appearances of just mockups and black-box prototypes of Hi-def DVD technology at every CES over the past 7 years.  </p>

<p>Imagine HD-DVD as AOD (original name) and the restructuring of the DVD-Forum to gain the forum approval of the format.  Imagine Blu-ray branching out with a separate BD Association claiming they do not need DVD Forum approval for their format and gaining dozens of major companies as supporters.  </p>

<p>In other words, we did not need the Chinese alternatives to make this format war more complicated than it is.  Well, everything about HD is complicated, look for my other articles and you will see why (Is HDTV Complex Enough? is one).   </p>

<p>Let us do a short recap of the background before you run for that Tylenol.  </p>

<p><u><b>Back in January 2002, I wrote:</u></b></p>

<p>"Pioneer and LGE/Zenith have shown similar HD-DVD (before HD-DVD and BD split) prototype units on two CES shows in a row.  Panasonic introduced their unit for their first time at CES.</p>

<p>According to LGE, their unit uses a different coding/error correction than the Panasonic prototype, which makes the disk incompatible if media needs to be exchanged.  It seems that these HD-DVD prototype units could be subjected to the same multiple format competition of the regular DVD-recorders mentioned earlier (-RW, +RW and RAM).</p>

<p>Perhaps by the time the motion picture industry would allow them to be commercially available, the format war of DVD recorders might be over, and hopefully these HD units would have then a single de-facto format.  (Note in 2006: what a dreamer!)</p>

<p>The LGE/Zenith has provided detailed information about their unit on both years, and claimed that it could possibly include a HD-PVR and guides in the future.  Pioneer's information about their unit has been very restricted over the same period."</p>

<p><br />
<u><b>Back in January 2003, I wrote:</u></b></p>

<p>(Pages 66 to 68 of the 2003 report, free from this magazine).<br />
 <br />
"The Hi-def DVD technology has been demonstrated for its third year at CES, still as prototypes and mockups, but now from a wider variety of manufacturers.  This year there was some progress; a proposal of the HD-DVD system standard was submitted, and several industry meetings (with Hollywood) were held.  </p>

<p>There are also some designs developed that claimed to have backward compatibility, so HD-DVDs could be played back downgraded on regular DVD players.  However, unresolved copy protection issues and format wars might further delay the introduction of this technology.  </p>

<p>We hope that one day soon we would actually see final products using a single standard format.  The implementation of this technology would certainly accelerate the public interest on HDTV monitors."  </p>

<p></p>

<p>Well, we are 6 years later, does it sound familiar?</p>

<p>Please consult the 2003, 4, 5, and 6 reports to gain details about how this technology evolved.  The reports dedicate a separate section to the subject.  </p>

<p>However, at this time, let us address the part of the title that says: <b>Well, what else is out there?</b></p>

<p><br />
<b><u>Chinese Hi-def DVD?</b></u></p>

<p>As detailed in my January 2004 report, when the Chinese market released their format called EVD, about 20 million players were expected to be released to the market within the next couple of years following that announcement.  EVD chips were produced and the format was expected to be an enhancement of the current DVD format.  What happened after that?  Where are all those Hi-def DVD players in the US?  Where are the movies?</p>

<p>At that time, it was anticipated that the EVD format could certainly affect much more than the Chinese market, although it was believed that content providers have not been contacted yet, an important factor for a pre-recorded format to be successful.   </p>

<p>Any R&D lab or manufacturer could create a wonderful product if given enough resources and time, but the success of this type of product depends on the availability of appealing content, content that needs to be protected to become widely available.  No egg no chicken.  Would you buy a PC if no software were available?   </p>

<p>Let us concentrate a bit on the hardware.  </p>

<p>To actually get a feeling of how real was this new Chinese DVD for HD (EVD) player, I met at CES 2004 with representatives of the Chinese company that manufactures the finished product, Changhong, Sichuan, China. </p>

<p>According to the company's profile (summarized version back then) "Changhong Electric Co., Ltd was founded in 1958, their R&D teams have joint labs with Toshiba, SANYO, Philips, Panasonic, Motorola, to name a few.  Changhong was regarded as a miracle in China, it grew to be one of the 3 leading TV manufacturers in the world, the aggressive DVD product supplier in China, the biggest electronics component supplier in Asia, with sale's volumes over 2 billion US dollars". </p>

<p>They were looking for US importers of the EVD player at that time.  Back then I left pending a confirmation of the information below with the US project manager; unfortunately, neither the manager nor the firm's headquarters in China returned my emails and calls, but I include below what was disclosed at my CES 2004 meeting.  First, it was shocking to know that for their internal market the EVD player sells for approximately $120 (no zeros missing on this HD product). </p>

<p>Back in 2003 I mentioned: Those that have followed past events regarding DVD for HD (I am using that term to avoid confusing it with the HD-DVD named by Toshiba), will notice the large price difference with Sony's Blu-ray product released in Japan in<br />
<img src="/images/articles/evd-fact-sheet.jpg" alt="EVD Fact Sheet" align="right"><br />
April 03, which, although it is a recorder unit w/satellite tuner in Blu-Ray format, it sells for $3,800 (or over 30 times the EVD).</p>

<p>Likewise, other Blu-ray (unreleased) player products shown at CES for the past 3 years were generally estimated by their manufacturers at price points around $2,000 for their future introduction by 2004/5.  </p>

<p>Note in 2006:  actually the Pioneer Elite is about to be released for $1,800 by mid 2006, so the estimates above were no so far off for some models.  Other models were recently announced for about $1,000 like Sony and Samsung.  I am not discussing the HD DVD killer price points here, Blu-ray says that Toshiba is manipulating the price to gain market share, but Sony plans to follow the same approach with the Blu-ray Play Station 3 game-console, if we ever going to see that one out.</p>

<p>At that time, this Chinese EVD manufacturer expected that the product could sell in the US market for about $250, with a listed importer cost of $80 per unit ($ from the exporter list shown at the meeting).  </p>

<p>Back then (and maybe even now), its low price brought the market hope that the low price pressure introduced by EVD could make more affordable this technology sooner than experienced on previous format wars, such as the recordable DVD, DVD-Audio and SACD, and even the old Beta vs. VHS.  </p>

<p>At that time I said: Regardless of what the actual outcome of the EVD pricing with the blue-laser market would be, the pre-recorded playing format could only compete (and make pressure on the market) as long as the hardware and software are of "uncompromised HD quality and storage capacity" and the major Hollywood content providers support it.</p>

<p>Note in 2006: and we all know how messy that is, just add the content protection bullet vests of AACS (Advanced Access Content System), BD+, BD ROM Mark, and the ICT (Image Constraint Token) for down-resing analog connections, PVP-OPM and PVP-UAB for PC protection, the multiple video codecs, the multiple hi-bit audio formats, the HDMI specification upgrades everyone is using as excuse instead of saying the real reasons their products are not out, the lack of vision for not including 1080p outputs on HD DVD when playing discs that already are 1080p 24fps, etc. and we have quite a gridlock. </p>

<p>One particular item that I was waiting for clarification from this Chinese company back then (and I never got) was the storage capacity declared on the last two lines of the EVD Fact Sheet (>=105 minutes for 720p, >=50 minutes for 1080i), how much > was >?  I assumed less than one minute.  </p>

<p>The EVD company did not return messages to confirm pricing and specs, so without confirmation it was safer to assume that the maximum of 50 minutes of 1080i HD would not attract any major Hollywood studio for blockbuster feature films, maybe not even a Kung Fu movie from the Chinese movie making industry, but 105 minutes of 720p might.  The specs were provided as supplied at their CES booth (in Jan 2004). </p>

<p>Ironically, I said back then: "In the year 2004, we will witness how this format competition evolves and which Hollywood content provider/s would actually support the format; additionally, some of the Hi-def player manufacturers have already announced that they would release their Hi-def products starting this year (2004)."  </p>

<p>Note in 2006:  over three years and we are still in the waiting, only Toshiba released their first player a few weeks ago, with a handful of discs.</p>

<p>The following EVD photographs were taken at CES 2004:<br />
<img src="/images/articles/evd-500.jpg" alt="EVD Fact Sheet" align="left"><br />
<img src="/images/articles/evd-500-2.gif" alt="EVD Fact Sheet" align="right"><br clear=all></p>

<p>Please stay tuned for the next part of this series, appearing soon.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Rodolfo La Maestra</b>, <b>May  8, 2006 11:50 AM</b>
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
			<?=getComments(371)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Rodolfo La Maestra', 371)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/hidef-dvd-blue-laser-well-what-else-is-out-there.php" type="text/javascript" charset="utf-8"></script>
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