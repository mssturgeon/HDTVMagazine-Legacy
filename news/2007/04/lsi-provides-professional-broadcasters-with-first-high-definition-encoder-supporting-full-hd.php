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
		AND e.entry_id = 575";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 575 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 575 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 575";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2007/04/lsi-provides-professional-broadcasters-with-first-high-definition-encoder-supporting-full-hd.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 575";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD" />
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
	<title>HDTV Magazine - LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD</title>
	<meta name="keywords" content="real time, high definition, supporting full, our customers, compression formats, lsi, encoding, high, video, quality, media, platform, performance, solutions, time, content, full, well, while, our, level, compression, first, manufacturers, system" />
	<meta name="description" content="The press release from LSI (below) marks a beginning of 1080p distribution in ways other than optical disks. The encoding of a progressive source is not much more data consuming than it is for an interlaced version (sometimes less, and algorithms, like wine, are improving with time).  While many still ask why we need 1080p since so much of the installed base of HD today is no more than 720p? At least one answer to that question is simple. Manufacturers want to sell bigger and bigger screens and they are hell-bent-for-leather to outdo each other in marketing low-to-lowering cost 1080p projection--front and rear--devices that support those bigger screens. As you recall ten years ago the $10,000 price point for a 50 Inch rear tube-type projector plagued us, but changed dramatically over the ensuing years to one tenth of that cost. Another answer to why 1080p is the fact that the dedicated media room is now an essential space in our homes, especially if we have any ideas toward selling that home. Home builders have also built into their offerings the &quot;media room&quot; once called a den. These dedicated room make forward projection with larger and larger screen sizes practical. While dedicated home theater has been around for years in the homes of the affluent one can feel the second wave about to break from within the mainstream and While well-programmed 720p signals are more-than-passable when viewed under 100 inch diagonal screen limitations of that format appear shortly thereafter. While the 42 to 70 inch non-front projection displays are nice and deliver a &quot;knock out&quot; punch in contrast to our old NTSC standard no comparison is easily found when a big, big screen lights up with a 1080p Blu Ray or HD DVD disk (full 1080p HD source). That theater-like experience is what will excite this next wave of enthusiasm and the small package needed for a front projector is cheaper to produce than the materials-laden rear DLP or LCoS projectors. This factor may even help manufacturers recover margins again considering there if a reduction in the bill-of-materials and shipping costs. While manufacturers have typically relied upon their lower cost models to fuel their bottom line the big 100 inch and great screen is about to become a powerful economic driver. More money will undoubtedly pour into front projector research as the market begins its ascent. Once this market does take wing an endless array of associated products will fill the CE store aisles. Moore's Law will also be more in evidence since the electronics is being sold more so than is the cabinetry. Mass production of lenses and other components will allow for low-cost entry projectors but with handsome performance specifications. Oh happy day. Cable and satellite as well as broadcasting will have little choice but to stay competitive with a 1080p transmission service, at least for their most prized programs. ATSC is busy developing new system pathways forward to 1080p that are compatible with today's ATSC hardware. 

LSI is among the first of their kind to produce encoders capable of doing full HDTV (1080p). They have done so in anticipation of that day when telecasters competitively outfit their plants with 1080p encoders and get set with 1080p transmission capability. Oh happy day!
 
__Dale Cripps





&lt;strong&gt;LSI provides professional broadcasters with first High definition encoder supporting Full HD&lt;/strong&gt;

 
&lt;em&gt;The 1080p60-capable LSI DX-1810 platform enables real-time HD H.264, VC-1, and MPEG-2 audio-video encoding&lt;/em&gt;

 

MILPITAS, Calif., April 11, 2007 - LSI Corporation (NYSE: LSI) today introduced the industry's first high definition (HD) real-time encoder with performance and quality required to support 1080p60, the highest resolution format for HD content. The first member of the LSI Domino[X]TM Pro product family" />
	<meta name="title" content="LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2007/04/lsi-provides-professional-broadcasters-with-first-high-definition-encoder-supporting-full-hd.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The press release from LSI (below) marks a beginning of 1080p distribution in ways other than optical disks. The encoding of a progressive source is not much more data consuming than it is for an interlaced version (sometimes less, and algorithms, like wine, are improving with time).  While many still ask why we need 1080p since so much of the installed base of HD today is no more than 720p? At least one answer to that question is simple. Manufacturers want to sell bigger and bigger screens and they are hell-bent-for-leather to outdo each other in marketing low-to-lowering cost 1080p projection--front and rear--devices that support those bigger screens. As you recall ten years ago the $10,000 price point for a 50 Inch rear tube-type projector plagued us, but changed dramatically over the ensuing years to one tenth of that cost. Another answer to why 1080p is the fact that the dedicated media room is now an essential space in our homes, especially if we have any ideas toward selling that home. Home builders have also built into their offerings the &quot;media room&quot; once called a den. These dedicated room make forward projection with larger and larger screen sizes practical. While dedicated home theater has been around for years in the homes of the affluent one can feel the second wave about to break from within the mainstream and While well-programmed 720p signals are more-than-passable when viewed under 100 inch diagonal screen limitations of that format appear shortly thereafter. While the 42 to 70 inch non-front projection displays are nice and deliver a &quot;knock out&quot; punch in contrast to our old NTSC standard no comparison is easily found when a big, big screen lights up with a 1080p Blu Ray or HD DVD disk (full 1080p HD source). That theater-like experience is what will excite this next wave of enthusiasm and the small package needed for a front projector is cheaper to produce than the materials-laden rear DLP or LCoS projectors. This factor may even help manufacturers recover margins again considering there if a reduction in the bill-of-materials and shipping costs. While manufacturers have typically relied upon their lower cost models to fuel their bottom line the big 100 inch and great screen is about to become a powerful economic driver. More money will undoubtedly pour into front projector research as the market begins its ascent. Once this market does take wing an endless array of associated products will fill the CE store aisles. Moore's Law will also be more in evidence since the electronics is being sold more so than is the cabinetry. Mass production of lenses and other components will allow for low-cost entry projectors but with handsome performance specifications. Oh happy day. Cable and satellite as well as broadcasting will have little choice but to stay competitive with a 1080p transmission service, at least for their most prized programs. ATSC is busy developing new system pathways forward to 1080p that are compatible with today's ATSC hardware. 

LSI is among the first of their kind to produce encoders capable of doing full HDTV (1080p). They have done so in anticipation of that day when telecasters competitively outfit their plants with 1080p encoders and get set with 1080p transmission capability. Oh happy day!
 
__Dale Cripps





&lt;strong&gt;LSI provides professional broadcasters with first High definition encoder supporting Full HD&lt;/strong&gt;

 
&lt;em&gt;The 1080p60-capable LSI DX-1810 platform enables real-time HD H.264, VC-1, and MPEG-2 audio-video encoding&lt;/em&gt;

 

MILPITAS, Calif., April 11, 2007 - LSI Corporation (NYSE: LSI) today introduced the industry's first high definition (HD) real-time encoder with performance and quality required to support 1080p60, the highest resolution format for HD content. The first member of the LSI Domino[X]TM Pro product family" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=575', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2007/04/lsi-provides-professional-broadcasters-with-first-high-definition-encoder-supporting-full-hd.php">LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>April 11, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=262&category=Business & Investment">Business & Investment</a></b>, <b><a href="/category.php?id=262&category=Business & Investment">Business & Investment</a></b>, <b><a href="/category.php?id=268&category=Politics & Policy">Politics & Policy</a></b>, <b><a href="/category.php?id=270&category=Programming">Programming</a></b>, <b><a href="/category.php?id=272&category=Technology">Technology</a></b>
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
				<p class="editorial">The press release from LSI (below) marks a beginning of 1080p distribution in ways other than optical disks. The encoding of a progressive source is not much more data consuming than it is for an interlaced version (sometimes less, and algorithms, like wine, are improving with time).  While many still ask why we need 1080p since so much of the installed base of HD today is no more than 720p? At least one answer to that question is simple. Manufacturers want to sell bigger and bigger screens and they are hell-bent-for-leather to outdo each other in marketing low-to-lowering cost 1080p projection--front and rear--devices that support those bigger screens. As you recall ten years ago the $10,000 price point for a 50 Inch rear tube-type projector plagued us, but changed dramatically over the ensuing years to one tenth of that cost. Another answer to why 1080p is the fact that the dedicated media room is now an essential space in our homes, especially if we have any ideas toward selling that home. Home builders have also built into their offerings the "media room" once called a den. These dedicated room make forward projection with larger and larger screen sizes practical. While dedicated home theater has been around for years in the homes of the affluent one can feel the second wave about to break from within the mainstream and While well-programmed 720p signals are more-than-passable when viewed under 100 inch diagonal screen limitations of that format appear shortly thereafter. While the 42 to 70 inch non-front projection displays are nice and deliver a "knock out" punch in contrast to our old NTSC standard no comparison is easily found when a big, big screen lights up with a 1080p Blu Ray or HD DVD disk (full 1080p HD source). That theater-like experience is what will excite this next wave of enthusiasm and the small package needed for a front projector is cheaper to produce than the materials-laden rear DLP or LCoS projectors. This factor may even help manufacturers recover margins again considering there if a reduction in the bill-of-materials and shipping costs. While manufacturers have typically relied upon their lower cost models to fuel their bottom line the big 100 inch and great screen is about to become a powerful economic driver. More money will undoubtedly pour into front projector research as the market begins its ascent. Once this market does take wing an endless array of associated products will fill the CE store aisles. Moore's Law will also be more in evidence since the electronics is being sold more so than is the cabinetry. Mass production of lenses and other components will allow for low-cost entry projectors but with handsome performance specifications. Oh happy day. Cable and satellite as well as broadcasting will have little choice but to stay competitive with a 1080p transmission service, at least for their most prized programs. ATSC is busy developing new system pathways forward to 1080p that are compatible with today's ATSC hardware.<br /><br />LSI is among the first of their kind to produce encoders capable of doing full HDTV (1080p). They have done so in anticipation of that day when telecasters competitively outfit their plants with 1080p encoders and get set with 1080p transmission capability. Oh happy day!<br /><br />__Dale Cripps</p>

<p><strong>LSI Provides Professional Broadcasters with First High Definition Encoder Supporting Full HD</strong></p>

<p> <br />
<em>The 1080p60-capable LSI DX-1810 platform enables real-time HD H.264, VC-1, and MPEG-2 audio-video encoding</em></p>

<p> </p>

<p>MILPITAS, Calif., April 11, 2007 - LSI Corporation (NYSE: LSI) today introduced the industry's first high definition (HD) real-time encoder with performance and quality required to support 1080p60, the highest resolution format for HD content. The first member of the LSI Domino[X]TM Pro product family, the DX-1810 is a scalable and future-proof platform that supports H.264, MPEG-2 and VC-1 compression formats. The high output quality of the LSI DX-1810 makes it an ideal encoding solution for TV program content delivery as well as for Blu-ray and HD DVD real-time authoring systems.</p>

<p>"Satellite TV, cable TV and IPTV service providers want high quality encoding solutions that can be upgraded as compression algorithms improve and as new compression formats are introduced," said Michelle Abraham, senior analyst, In-Stat. "The scalability and programmability of solutions such as the LSI DX-1810 encoding platform give the performance, quality and flexibility required by this class of sophisticated customers."</p>

<p>With the advent of display and digital TV technologies supporting full HD video, content providers, service operators and system manufacturers are exploring ways to deliver premium content to consumers. The DX-1810 platform achieves the performance required for high quality 1080p60 encoding at the lowest possible bit-rate by leveraging the scalability of the programmable LSI Domino[X] media processor architecture. Using interconnected media processors with special high-speed interfaces, the DX-1810 platform can be scaled to perform at 3500 billion operations per second (BOPS) or 3.5 tera operations per second (TOPS) for audio-video processing and 12 BOPS for high-level image analysis, statistical multiplexing and system level operations. The scalability of the LSI solution allows additional media processors to be added for HD level pre-processing such as motion compensated temporal filtering (MCTF) to improve video quality or to create dual- or triple-pass HD encoders for bandwidth constrained multi-channel broadcast applications.</p>

<p>"With VC-1 growing in popularity among content providers for high-definition delivery, the fact that LSI is now offering real-time HD encoding for VC-1 is an important development for the industry," said Tim Harader, senior business development manager of Consumer Media Technology at Microsoft Corp.</p>

<p>"VC-1, through Windows Media Video, continues to be the dominant video format for Internet and enterprise delivery, as well as for popular services such as the Xbox Live Marketplace and leading optical formats such as HD DVD."  </p>

<p>"We understand our customers' challenges in the broadcast space and we are pleased to provide a family of products that address their immediate as well as future needs," said Bob Saffari, senior director of marketing, Advanced Video Products, LSI Corporation. "Unlike point solutions that only support a single standard with limited capabilities, the LSI DX-1810 scalable HD encoding platform is programmable, flexible and has the high performance our customers need to deliver best-in-class H.264, MPEG-2 and VC-1 solutions. With approximately 3.5 TOPS of processing power, our customers can develop and customize sophisticated algorithms to give them a competitive advantage."</p>

<p>To ensure this high level of performance does not become outdated, the LSI DX-1810 has a programmable architecture with common APIs. This feature makes it easy to upgrade the encoding quality and reduce the output bit-rate as improvements are made to the H.264, MPEG-2 and VC-1 encoding algorithms or to support new compression formats as they become available.  Reducing development risk and system-level costs, the LSI DX-1810 is backwards compatible with other LSI encoding solutions, allowing re-use of previously developed software.</p>

<p>LSI will be demonstrating its pioneering video compression technology and solutions at the National Association of Broadcasters (NAB) show, April 16-19 in Las Vegas, at booth #SU13517.</p>

<p> Please visit http://dominox.lsi.com.</p>

<p> </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>April 11, 2007 12:45 PM</b>
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
			<?=getComments(575)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 575)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/04/lsi-provides-professional-broadcasters-with-first-high-definition-encoder-supporting-full-hd.php" type="text/javascript" charset="utf-8"></script>
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