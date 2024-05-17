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
		AND e.entry_id = 565";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Richard Fisher" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 565 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 565 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 565";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 565";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HD Waveform 10 - Dynamic Iris and Gamma" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HD Waveform 10 - Dynamic Iris and Gamma" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HD Waveform 10 - Dynamic Iris and Gamma" />
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
	<title>HDTV Magazine - HD Waveform 10 - Dynamic Iris and Gamma</title>
	<meta name="keywords" content="dynamic range, light output, contrast ratio, peak white, gamma ire, gamma, iris, ire, dynamic, image, black, light, response, video, contrast, output, system, does, peak, range, pattern, ratio, using, imaging, full" />
	<meta name="description" content="Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature degrades overall image performance, why it sells product and why, for some technologies, it may be needed to be competitive.

&lt;B&gt;Iris&lt;/B&gt;
One way to improve dynamic range and measured contrast ratio is to employ an iris. An iris typically decreases..." />
	<meta name="title" content="HD Waveform 10 - Dynamic Iris and Gamma" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HD Waveform 10 - Dynamic Iris and Gamma" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature degrades overall image performance, why it sells product and why, for some technologies, it may be needed to be competitive.

&lt;B&gt;Iris&lt;/B&gt;
One way to improve dynamic range and measured contrast ratio is to employ an iris. An iris typically decreases..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=565', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php">HD Waveform 10 - Dynamic Iris and Gamma</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Richard Fisher</b> on <b>March 26, 2007</b>
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
				<p class="editorial">"HD Waveform" is a series of articles published over the past few years and originally made available only to subscribers of HDTV Magazine. It is authored by Richard Fisher and was born out of 20+ years in the industry and discussion among HDTV Magazine membership concerning faithful reporting. HD Waveform goes into great detail, providing conclusions based in science and is therefore suitable for both consumers and professionals alike. In many cases these conclusions will seem at odds with what many are hearing "on the street" and from marketers. Waveform is for those who care about quality, performance and reasonable scientific conclusions. Feel free to read the rest of <a href="/forum/viewforum.php?f=103">The HD Waveform Series</a>, as originally published.</p>

<p>Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature does not meet video standards, why it sells product and why, for some technologies, it may be needed to be competitive.</p>

<p>To shorten the article some terms have embedded links, blue, for a full definition of the term.</p>

<p><B>Iris</B><br />
One way to improve dynamic range and measured contrast ratio is to employ an iris. <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=86">An iris</a> typically decreases the light going to the panels or imager, or can also be on the output side reducing the light entering the lens after the imager. A manual iris allows the end user to employ a true brightness adjustment for the best blacks in their application. Reducing light output to the lens also improves the intrafield contrast ratio or actual available dynamic range when you have a mix of bright and dark within the same frame. Many higher end front projectors employ a manual iris. The disadvantage is that no matter where you set it, you have increased or decreased the light output linearly; Blacks may look blacker but peak white also drops in output as well. What if we had an iris that could change its setting on the fly based on image content such that it closes up during dark scenes for better blacks and opens up during bright scenes for peak light output, thereby creating a wider light output capability? Hook up a motor to a high speed iris mechanism and viola, you have an auto iris. While a good start, that alone cannot change the natural dynamic range of the technology. It only changes light output and one has to be very careful of when and how fast it changes or the viewer will catch this process in motion which is often times referred to as breathing.</p>

<p><B>Gamma</B><br />
What if you could change brightness and contrast levels, gamma, on the fly and better yet do it at multiple specific points in the video signal based on image content? Viola, you have the ability to create the perception of more dynamic range. <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Gamma</a> is the difference in response between two levels and for video we use the industry standard of 2.2. Video gamma is also a non linear response determined with an exponential equation rather than simple multiplication. A video signal is broken up into IRE levels where 0 IRE is peak black and 100 IRE is peak white. In terms of overall gamma, if it is less than 2.2 the image becomes flat, dull or washed-out and if it is more than 2.2 the image becomes dynamic, bright or aggressive. When manipulating gamma you have two brick walls, peak white and peak black which you cannot go beyond. As an example that means you cannot increase the gamma response from 60-100 IRE without decreasing the gamma from 0-59 IRE. You cannot increase the gamma from 40-70 IRE without decreasing gamma from 0-39 IRE and/or from 71-100 IRE. You have to rob Paul to pay Peter; there is no other way when playing the gamma game on the input signal or you will induce clipping errors that will be quite visible. It is possible to overcome this brick wall of a video signal by changing the gamma response at the imaging device but then you face the brick wall of what the device is capable of and that is typically maxed out anyway when using a <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">D65 color temperature</a> as a reference. This is a rare occurrence and bears little merit for discussion</p>

<p><B>Black is an Illusion</B><br />
When it comes to imaging, black is the absence of light and therefore has perceptual qualities related to optical illusion. We can take a display with poor blacks and with the right pattern, such as a full field 0 IRE pattern (black), make that aspect quite prominent. We can also improve our perception of that level of black by simply introducing a 50 IRE window in the middle of the 0 IRE full raster pattern. I can make the black seem even blacker by increasing that window to 100IRE providing the greatest amount of light difference. By providing that comparison to your eyes the black appears blacker, you perceive a greater dynamic range, but it is an optical illusion! Technically, measurement would show the far more likely result that light from the window is leaking into the black area which not only reduces your black level but can also hide subtle levels of black. This is called intrafield contrast ratio putting yet another spin on the optical illusion of black!</p>

<p><B>Putting All 3 Together</B><br />
The ultimate goal for this system is to create a perceptual increase in dynamic image. The best explanation uses two image extremes. Using a dark scene with no real peak white, the iris closes up improving real black level. The gamma is then expanded from say 0-50 IRE reducing the gamma from 50-100IRE. This will create a more dynamic presentation for the dark image and blacks will be perceived as even blacker since those areas that do have light have been increased, creating a greater dynamic difference; the optical illusion of black. Using a bright scene with no real peak black the iris opens up improving real light output. The gamma is then expanded from say 50-100 IRE reducing the gamma from 0-50 IRE. This will also create a more dynamic presentation and blacks will be perceived as even blacker since those areas that do not have light have been decreased creating a greater dynamic difference; the optical illusion of black. In no way does this simplistic example represent the far more complex nature of this system and its implementation, but hopefully you have an elementary understanding of how this is done and why it can make a mess of things.</p>

<p><B>Objective Measurements</B><br />
Does it work? Why of course! It does create the desired perception for the viewer of better dynamic range and allows the manufacturer to claim greater contrast ratios on their specs. Close up the iris for the peak black measurement and open it up for peak white measurement and you can only get a larger number than one without an iris. From a current Panasonic PTAE1000 review after ISF calibration:</p>

<p>With the Dynamic Iris OFF at 96 lamp hours I obtained 367fl at 100IRE and .522fl for 0IRE yielding a contrast ratio of 703:1.</p>

<p>With Dynamic Iris ON I obtained 715fl at 100IRE and .503fl for 0IRE yielding a contrast ratio of 1421:1.</p>

<p>If we go check the specs at Projector Central Panasonic is claiming a contrast ratio of 11000! This is defined as full on, full off. Historically this is defined as the projector using a 100IRE window or raster with all three colors maxed out for the 100IRE reading, not even remotely close to D65, nor really viewable, and then the projector turned off! Obviously that has nothing to do with actual imaging and is a flawed test, yet they are doing it. Nearly all of the manufacturers are following this testing procedure to remain on equal footing in the market. The human eye is capable of about 800:1 for any given scene whether on screen or in real life. Does a real contrast ratio of 11000:1 have any value? The point is this is nothing but specification marketing shenanigans that tell the consumer or imaging professional little about actual performance.</p>

<p>Getting back to the magic trick of a Dynamic iris, the ON number is very impressive in the real world of peak white and black contrast ratios! This test implies nearly a doubling in contrast ratio yet this is not the perceptual experience you will have. All an iris can change is the light output to the imager, not the native dynamic range of the technology. What really puts this in perspective is switching the iris on and off with paused images. With the right image you can see a difference in light output yet with most the difference is quite subtle. Ultimately the biggest difference you see with this test is a change of the gamma response within the image rather than simple light output.</p>

<p>If the end user does not know what the image should look like, no references, they likely will not detect that it is wrong either hence the acceptance and popularity in the mass market. No matter what, it will never provide an accurate image due to the artifacts of an incorrect gamma response. The first two gamma plots of a window and full field pattern represent the calibrated response with iris turned off as the light output changes from 0-100 IRE in 10 IRE steps. The dotted line presents the desired response curve so please ignore the average gamma calculation.</p>

<p>Full Field Pattern / Window Pattern<br />
<img src="/images/articles/waveform/patterns.jpg" alt="Full Field Pattern / Window Pattern" /></p>

<p>Window Pattern<br />
<img src="/images/articles/waveform/irisOFFwindow.jpg" alt="Window Pattern" /></p>

<p>Full Field Pattern<br />
<img src="/images/articles/waveform/irisOFFfull.jpg" alt="Full Field Pattern" /></p>

<p>As side note, you may have noticed that these two plots are not exactly the same and that, too, is an error and an unexpected error at that because as a lamp based display there is no reason for the light output to change. This error is directly related to the design of the product and it appears gamma shifting is still taking place for whatever reason. This was also reflected in the calibration of this projector. It was a moving target rarely providing an identical response when retested for the same parameter. The forthcoming review covers this in depth.</p>

<p>The two gamma plots that follow are with the iris on showing the system manipulating gamma based on image content. The dotted line presents the desired response curve.</p>

<p>Window Pattern<br />
<img src="/images/articles/waveform/irisONwindow.jpg" alt="Window Pattern" /></p>

<p>Full Field Pattern<br />
<img src="/images/articles/waveform/irisONfull.jpg" alt="Full Field Pattern" /></p>

<p>The goal of this article has been achieved. Testing clearly shows the system in action creating a non-linear response and also changing that response based on image content. What these tests do not reveal is where the system would be manipulating gamma within a variety of far more complex real world images. Unfortunately such depth is beyond the time and resources of this reviewer as well as article length so instead I provide my subjective observations.</p>

<p>When first implemented these systems clearly showed problems either from breathing of the iris or poorly implemented gamma manipulation. While products employing these systems for 2007 have greatly advanced eliminating obvious errors, you can't rob Paul to pay Peter without that image appearing flat in some aspect when it should not. If you have a reference of what the image should look like you will also recognize that such systems clearly change the response; it does not look the same. For the Panasonic, the net effect on the image was subtle for the most part providing a difference that perceptually did appear to improve dynamic range. Testing with common video sources there were no obvious image artifacts to be seen tipping its hand to the viewer but those sources also allow far more leniency with errors. That was not the case with a PC source which required I turn off the auto iris to get a proper image with some content due to a flattening of the upper IRE response. No matter what you may perceive using these products they will not do imaging science with the system turned on; the image is artificial and does not represent the original; it does not meet video industry standards. Someone who masters video or is involved with video setup for mass distribution should not be using a display with such a system that does not allow it to be turned off.</p>

<p><B>Turning it Off</B><br />
If the system has been employed simply to improve sales and marketing specs then turning it off is of no concern. As a system, in most cases you cannot turn off just one or the other feature but the <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/">ISF community</a> has figured out a way on some displays to turn off the gamma manipulation and manually adjust the iris for better blacks for a particular application. Some projectors employ a manual iris for that very purpose which comes with the side benefit of better intrafield contrast ratios but that does little to change the natural dynamic range of the imaging technology taking me to the next point.</p>

<p>It could be argued that transmissive LCD needs this process to create a competitive dynamic image and there may be others. I have yet to review any of the reflective LCD products yet based on what I am reading from other reviewers some versions appear to have similar properties. The problem here is natural dynamic range of the technology without any slight of hand. Transmissive LCD clearly suffers from this dilemma and if using CRT as our reference none of the microdisplay technologies qualify. On the other hand if we use film as our reference point for black current DLP microdisplay technology since 2006 has come quite close to that level of response with no gimmicks required.</p>

<p>A point of contention with this system occurs when making direct comparisons of performance. If I am building a DLP projector that meets video standards and setup a demo comparing it to transmissive LCD the main comparison is going to be how my projector looks without such a system and how the LCD looks with it turned off providing a direct comparison of the natural dynamic range of both technologies based on a calibrated response that meets video standards. Many have claimed such comparisons to be unfair yet scientifically speaking it is not only fair but required to compare apples to apples. Naturally the LCD will suffer. If the LCD is allowed to use the system then ultimately you will get two different images with the LCD potentially perceived as more dynamic and if both are pleasing to the eye the only argument left is accuracy, video standards and your perception. Choose!</p>

<p><B>In Perspective</B><br />
If you are looking for performance that meets video imaging standards the measurements, observations and science conclusively show that the use of a dynamic iris and gamma manipulation is clearly not the path of high fidelity imaging.</p>

<p>These kinds of imaging antics and tricks have been going on for decades and are nearly always related to a performance flaw of the technology, implementation and design or cost cutting. Using such tricks to yield better marketing and sales specs for a technology that performs adequately to begin with is old hat as well. Whether or not you want a product that forces or needs such tricks is your call.</p>

<p><B>Links</B><br />
<a href="/forum/viewtopic.php?t=3787">Contrast Ratio</a></p>

<p><a href="/forum/viewforum.php?f=103">Waveform Series</a><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Richard Fisher</b>, <b>March 26, 2007 11:28 AM</b>
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
			<?=getComments(565)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Richard Fisher', 565)?>

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
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/js/jquery-plugins/jcaption.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?=$container?> img').jcaption({
				copyAlignmentToClass: true
			});
		});
	</script>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/hd-waveform-10-dynamic-iris-and-gamma.php" type="text/javascript" charset="utf-8"></script>
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