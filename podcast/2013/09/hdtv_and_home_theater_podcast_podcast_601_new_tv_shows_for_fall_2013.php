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
		AND e.entry_id = 5147";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5147 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 5147 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 5147";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/podcast/2013/09/hdtv-and-home-theater-podcast-podcast-601-new-tv-shows-for-fall-2013.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (9) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 5147";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013" />
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
	<title>HDTV Magazine - HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013</title>
	<meta name="keywords" content="new series, series nbc, series fox, series cbs, cbs sep, new, series, cbs, abc, fox, nbc, starring, sep, family, tuesday, thursday, oct, shows, time, back, monday, fall, comedy, base, men" />
	<meta name="description" content="It&amp;#039;s that time of year again - a whole fall slate full of new HDTV shows. These new shows give ut the opportunity to make new friends, share some laughs, and have an adventure or two, all from the comfort of your comfy living room sofa. When we were kids we looked forward to Christmas and the first day of summer.  As adults, we look forward to the fall more than anything else. Yes, the kids are back in school, but also our favorite shows are coming back with new episodes and we get the opportunity to fill the DVR with brand new series." />
	<meta name="title" content="HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/podcast/2013/09/hdtv-and-home-theater-podcast-podcast-601-new-tv-shows-for-fall-2013.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="It&amp;#039;s that time of year again - a whole fall slate full of new HDTV shows. These new shows give ut the opportunity to make new friends, share some laughs, and have an adventure or two, all from the comfort of your comfy living room sofa. When we were kids we looked forward to Christmas and the first day of summer.  As adults, we look forward to the fall more than anything else. Yes, the kids are back in school, but also our favorite shows are coming back with new episodes and we get the opportunity to fill the DVR with brand new series." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Podcasts Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=5147', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/podcast/2013/09/hdtv-and-home-theater-podcast-podcast-601-new-tv-shows-for-fall-2013.php">HDTV and Home Theater Podcast - Podcast #601: New TV Shows for Fall 2013</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>September 20, 2013</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=456&category=Cable HDTV">Cable HDTV</a></b>, <b><a href="/category.php?id=502&category=LED (LCD) HDTVs">LED (LCD) HDTVs</a></b>
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
				<h3>New TV Shows for Fall 2013</h3>
<p dir="ltr">It&#8217;s that time of year again &#8211; a whole fall slate full of new HDTV shows. These new shows give ut the opportunity to make new friends, share some laughs, and have an adventure or two, all from the comfort of your comfy living room sofa. When we were kids we looked forward to Christmas and the first day of summer.  As adults, we look forward to the fall more than anything else. Yes, the kids are back in school, but also our favorite shows are coming back with new episodes and we get the opportunity to fill the DVR with brand new series.</p>
<h4>ABC</h4>
<ul>
<li>
<p dir="ltr"><strong>Betrayal </strong>(Sunday) &#8211; A photographer begins an affair with an attorney for a powerful family, but their secret is in jeopardy when her ambitious husband becomes the prosecutor in a high-profile murder case in which her paramour is defending the prime suspect.</p>
</li>
<li>
<p dir="ltr"><strong>Marvel&#8217;s Agents of S.H.I.E.L.D.</strong> (Tuesday) &#8211; Agent Phil Coulson assembles a team to investigate extraordinary people and events around the world in this action-adventure series.</p>
</li>
<li>
<p dir="ltr"><strong>The Goldbergs</strong> (Tuesday) &#8211; A nostalgic comedy series about kids growing up in a dysfunctional family in the 1980s. Based on creator Adam F. Goldberg&#8217;s childhood.</p>
</li>
<li>
<p dir="ltr"><strong>Trophy Wife</strong> (Tuesday) &#8211; A young woman marries an older man, inheriting difficult relationships with his three kids and two ex-wives. Starring Bradley Whitford.</p>
</li>
<li>
<p dir="ltr"><strong>Lucky 7</strong> (Tuesday) &#8211; Seven coworkers at a gas station in Queens play the lottery as a group every week and dream about how their lives would change if they ever won, but they get more than they bargained for when they actually hit the jackpot.</p>
</li>
<li>
<p dir="ltr"><strong>Back in the Game</strong> (Wednesday) &#8211; A divorced single mom and former softball all-star coaches her son&#8217;s baseball team of misfits after they&#8217;re rejected by the local Little League squad, and gets help from her beer-guzzling father, a once-promising ballplayer with whom she&#8217;s forced to live after being estranged for years. Starring James Caan.</p>
</li>
<li>
<p dir="ltr"><strong>Super Fun Night</strong> (Wednesday) &#8211; A big promotion alters an attorney&#8217;s relationship with her two best friends in this sitcom. Starring Rebel Wilson.</p>
</li>
<li>
<p dir="ltr"><strong>Once Upon a Time in Wonderland</strong> (Thursday) &#8211; Victorian England, the young and beautiful Alice (Sophie Lowe) tells a tale of a strange new land that exists on the other side of a rabbit hole.</p>
</li>
</ul>
<h4>NBC</h4>
<ul>
<li>
<p dir="ltr"><strong>The Blacklist</strong> (Monday) &#8211; A most-wanted fugitive works with a rookie FBI profiler to take down criminals and terrorists in this crime series. Starring James Spader.</p>
</li>
<li>
<p dir="ltr"><strong>Ironside</strong> (Wednesday) &#8211; A paraplegic police detective solves criminal cases in New York City. Starring Blair Underwood.</p>
</li>
<li>
<p dir="ltr"><strong>Welcome to the Family</strong> (Thursday) &#8211; A white family and a Latino family blend when their offspring fall in love in this comedy series.</p>
</li>
<li>
<p dir="ltr"><strong>Sean Saves the World</strong> (Thursday) &#8211; A comedy centering on a divorced gay father trying to balance the demands of his life. His juggle struggle includes focusing on his successful career, dealing with his meddling mother and raising his teen daughter. Starring Sean Hayes.</p>
</li>
<li>
<p dir="ltr"><strong>The Michael J. Fox Show</strong> (Thursday) &#8211; One of New York&#8217;s most beloved news anchors, (Fox), put his career on hold to focus on his health after he was diagnosed with Parkinson&#8217;s. But now five years later, it&#8217;s time for him to get back to work.</p>
</li>
<li>
<p dir="ltr"><strong>Dracula</strong> (Friday) &#8211; Dracula is resurrected in 19th century London and seeks revenge against those who cursed him with immortality centuries earlier.</p>
</li>
</ul>
<h4>CBS</h4>
<ul>
<li>
<p dir="ltr"><strong>We Are Men</strong> (Monday) &#8211; Four single men bond at a short-term rental complex in this comedy series. Starring Jerry O&#8217;Connell and Tony Shalhoub.</p>
</li>
<li>
<p dir="ltr"><strong>Mom </strong>(Monday) &#8211; A comedy centering on a newly sober single mother trying to raise two children while dealing with her overly critical mother and working as a waitress in Napa Valley. Executive producers include Chuck Lorre (&#8216;Two and a Half Men&#8217; and &#8216;The Big Bang Theory&#8217;). Starring Anna Faris and Allison Janney.</p>
</li>
<li>
<p dir="ltr"><strong>Hostages </strong>(Monday) &#8211; A surgeon about to operate on the U.S. president is ordered to kill him by a rogue FBI agent who is holding her family hostage. Starring Toni Collette and Dylan McDermott.</p>
</li>
<li>
<p dir="ltr"><strong>The Millers</strong> (Thursday) &#8211; A recently divorced news reporter&#8217;s single life is disrupted when his mom moves in with him, and his dad moves in with his sister. Starring Will Arnett.</p>
</li>
<li>
<p dir="ltr"><strong>The Crazy Ones</strong> (Thursday) &#8211;  An eccentric advertising exec and his levelheaded daughter cater to top-tier clients at their ad firm. Starring Robin Williams and Sarah Michelle Gellar.</p>
</li>
</ul>
<h4>FOX</h4>
<ul>
<li>
<p dir="ltr"><strong>Sleepy Hollow</strong> (Monday) &#8211; An update of Washington Irving&#8217;s classic tale about Ichabod Crane, who wakes up in the 21st century but finds his 18th-century nemesis, the Headless Horseman, has also come along for the ride. Features John Cho.</p>
</li>
<li>
<p dir="ltr"><strong>Dads </strong>(Tuesday) &#8211; Two successful video-game developers take in their hard-to-live-with fathers in this sitcom. Starring Seth Green and Giovanni Ribisi.</p>
</li>
<li>
<p dir="ltr"><strong>Brooklyn Nine-Nine</strong> (Tuesday) &#8211; A sitcom following the lives of an eclectic group of detectives in a New York precinct, including one slacker who is forced to shape up when he gets a new boss. Starring Andy Samberg and Andre Braugher.</p>
</li>
<li>
<p dir="ltr"><strong>Masterchef Junior</strong> (Friday) &#8211; Talented young cooks between the ages of 8 and 13 compete in this Gordon Ramsay reality series.</p>
</li>
</ul>
<h4>CW</h4>
<ul>
<li>
<p dir="ltr"><strong>The Originals</strong> (Tuesday) &#8211; A spin-off of &#8220;The Vampire Diaries&#8221; focuses on the original vampire family who return to New Orleans to reclaim the city they helped build that is now under the control of a diabolical vampire named Marcel.</p>
</li>
<li>
<p dir="ltr"><strong>The Tomorrow People</strong> (Wednesday) &#8211; Humans born with paranormal abilities are hunted by a paramilitary group of scientists who believe they are a threat to mankind.</p>
</li>
<li>
<p dir="ltr"><strong>Reign</strong> (Thursday) &#8211; The previously unknown and untold story of Mary Queen of Scots rise to power when she arrives in France as a 15-year-old to formalize her arranged marriage to a prince.</p>
</li>
</ul>
<p>&nbsp;</p>
<h4>Premiere Dates</h4>
<p dir="ltr">Sep. 16</p>
<p dir="ltr">8pm: Bones (moves to Fridays starting Nov. 8) (Fox)</p>
<p dir="ltr">9pm: Sleepy Hollow (new series) (Fox)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 17</p>
<p dir="ltr">8pm: Dads (new series) (Fox)</p>
<p dir="ltr">8pm: Dancing With the Stars (ABC)</p>
<p dir="ltr">8:30pm: Brooklyn Nine-Nine (new series) (Fox)</p>
<p dir="ltr">9pm: New Girl (Fox)</p>
<p dir="ltr">9:30pm: The Mindy Project (Fox)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 18</p>
<p dir="ltr">8pm: Survivor (CBS)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 19</p>
<p dir="ltr">9pm: Glee (Fox)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 20</p>
<p dir="ltr">8pm: Last Man Standing (ABC)</p>
<p dir="ltr">8:30pm: The Neighbors (ABC)</p>
<p dir="ltr">9pm: Shark Tank (ABC)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 23</p>
<p dir="ltr">8pm: How I Met Your Mother (one-hour season premiere) (CBS)</p>
<p dir="ltr">8pm: The Voice (NBC)</p>
<p dir="ltr">9pm: 2 Broke Girls (CBS)</p>
<p dir="ltr">9:30pm: Mom (new series) (CBS)</p>
<p dir="ltr">10pm: Castle (ABC)</p>
<p dir="ltr">10pm: Hostages (new series) (CBS)</p>
<p dir="ltr">10pm: The Blacklist (new series) (NBC)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 24</p>
<p dir="ltr">8pm: Marvel&#8217;s Agents of S.H.I.E.L.D. (ABC)</p>
<p dir="ltr">8pm: NCIS (CBS)</p>
<p dir="ltr">9pm: The Goldbergs (ABC)</p>
<p dir="ltr">9pm: NCIS: Los Angeles (CBS)</p>
<p dir="ltr">9:30pm: Tropy Wife (ABC)</p>
<p dir="ltr">10pm: Lucky 7 (ABC)</p>
<p dir="ltr">10pm: Person of Interest (CBS)</p>
<p dir="ltr">10pm: Chicago Fire (NBC)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 25</p>
<p dir="ltr">8pm: The Middle (ABC)</p>
<p dir="ltr">8pm: Revolution (NBC)</p>
<p dir="ltr">8: 30pm: Back in the Game (ABC)</p>
<p dir="ltr">9pm: Modern Family (ABC)</p>
<p dir="ltr">9pm: Criminal Minds (CBS)</p>
<p dir="ltr">9pm: Law &amp; Order: SVU (NBC)</p>
<p dir="ltr">10pm: Nashville (ABC)</p>
<p dir="ltr">10pm: CSI (CBS)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 26</p>
<p dir="ltr">8pm: The Big Bang Theory (CBS)</p>
<p dir="ltr">8pm: Parks and Recreation (NBC)</p>
<p dir="ltr">8:30pm: The Millers (new series) (CBS)</p>
<p dir="ltr">9pm: Grey&#8217;s Anatomy (ABC)</p>
<p dir="ltr">9pm: The Crazy Ones (new series) (CBS)</p>
<p dir="ltr">9pm: The Michael J. Fox Show (new series) (NBC)</p>
<p dir="ltr">9:30pm: Two and a Half Men (CBS)</p>
<p dir="ltr">10pm: Elementary (CBS)</p>
<p dir="ltr">10pm: Parenthood (NBC)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 27</p>
<p dir="ltr">8pm: Undercover Boss (CBS)</p>
<p dir="ltr">8pm: MasterChef Junior (new series) (Fox)</p>
<p dir="ltr">9pm: Hawaii Five-O (CBS)</p>
<p dir="ltr">9pm: Dateline (NBC)</p>
<p dir="ltr">10pm: Blue Bloods (CBS)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 29</p>
<p dir="ltr">8pm: Once Upon a Time (ABC)</p>
<p dir="ltr">8pm: The Amazing Race (CBS)</p>
<p dir="ltr">8pm: The Simpsons (Fox)</p>
<p dir="ltr">8:30pm: Bob&#8217;s Burgers (Fox)</p>
<p dir="ltr">9pm: Revenge (ABC)</p>
<p dir="ltr">9pm: The Good Wife (CBS)</p>
<p dir="ltr">9pm: Family Guy (Fox)</p>
<p dir="ltr">9:30pm: American Dad (Fox)</p>
<p dir="ltr">10pm: Betrayal (ABC)</p>
<p dir="ltr">10pm: The Mentalist (CBS)</p>
<p>&nbsp;</p>
<p dir="ltr">Sep. 30</p>
<p dir="ltr">8:30pm: We Are Men (CBS)</p>
<p>&nbsp;</p>
<p dir="ltr">Oct. 2</p>
<p dir="ltr">9pm: Super Fun Night (ABC)</p>
<p dir="ltr">10pm: Ironside (new series) (NBC)</p>
<p>&nbsp;</p>
<p dir="ltr">Oct. 3</p>
<p dir="ltr">8:30pm: Welcome to the Family (new series) (NBC)</p>
<p dir="ltr">9pm: Sean Saves the World (new series) (NBC)</p>
<p dir="ltr">10pm: Scandal (ABC)</p>
<p>&nbsp;</p>
<p dir="ltr">Oct. 9</p>
<p dir="ltr">8pm: Arrow (CW)</p>
<p dir="ltr">9pm: The Tomorrow People (CW)</p>
<p dir="ltr">10pm American Horror Story (FX)</p>
<p>&nbsp;</p>
<p dir="ltr">Oct. 10</p>
<p dir="ltr">8pm: Once Upon a Time in Wonderland (ABC)</p>
<p dir="ltr">8pm: The Vampire Diaries (CW)</p>
<p dir="ltr">9pm: Reign (new series) (CW)</p>
<p>&nbsp;</p>
<p dir="ltr">Oct. 15</p>
<p dir="ltr">8pm: The Originals (new series) (CW)</p>
<p>&nbsp;</p>
<p dir="ltr">Oct. 25</p>
<p dir="ltr">8pm: The Carrie Diaries (CW)</p>
<p dir="ltr">9pm: Grimm (NBC)</p>
<p dir="ltr">10pm: Dracula (new series) (NBC)</p>
<p>&nbsp;</p>
<p dir="ltr">Nov. 4</p>
<p dir="ltr">8pm: Almost Human (new series) (Fox) &#8211; The series is set 35 years into the future when humans in the Los Angeles Police Department are paired up with life-like androids, as a detective who has a dislike for robots ends up being teamed up with one with emotional feelings. Stars Karl Urban (Dr. McCoy from the new Star Trek movies).</p>
<p>&nbsp;</p>
<p dir="ltr">Nov. 8</p>
<p dir="ltr"> 9pm: Raising Hope (Fox)</p>
<p dir="ltr">9: 30pm: Enlisted (new series) (Fox) &#8211; Three very different brothers, each enlisted in the US Army, find themselves all stationed at the same Florida military base. When the majority of the base is deployed overseas, the brothers are assigned to the Rear Detachment – the soldiers left behind to take care of the base. While working together, along with the other misfits on the base, the brothers are able to renew and strengthen their childhood bonds.</p>
<p>&nbsp;</p>
<h4>Not really 2013 anymore, but…</h4>
<p dir="ltr">Feb. 24</p>
<p dir="ltr">10pm: Intelligence (new series) (CBS) &#8211; The series follows a high-tech intelligence operative who is the first of his kind to have a microchip implanted in his brain. Endowed with this technology and ability, he is able to access and detect anything and anyone, but at the expense of taking risks or breaking protocol in order to protect national security, prompting his superior to assign him a Secret Service agent to make sure he does not get out of line, or, for that matter, into enemy hands. Stars Josh Holloway (Sawyer from Lost) and Marg Helgenberger (from CSI).</p>
<p>&nbsp;</p>
<p><a href="http://traffic.libsyn.com/hdtvpodcast/HDTV-2013-09-20.mp3">Download Episode #601</a></p>
<br />  
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>September 20, 2013 12:29 AM</b>
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
			<?=getComments(5147)?>
			<div class="dottedline"></div>

			<? if (9 != 7) echo getBoxMoreFromAuthor('The HT Guys', 5147)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/podcast/2013/09/hdtv-and-home-theater-podcast-podcast-601-new-tv-shows-for-fall-2013.php" type="text/javascript" charset="utf-8"></script>
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