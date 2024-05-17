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
		AND e.entry_id = 6";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 6 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 6 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 6";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/05/do-you-want-a-date-certain.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 6";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Do You Want A Date Certain?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Do You Want A Date Certain?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Do You Want A Date Certain?" />
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
	<title>HDTV Magazine - Do You Want A Date Certain?</title>
	<meta name="keywords" content="cable satellite, american homes, cut date, free air, digital television, cable, homes, cut, date, analog, air, satellite, dtv, television, signal, sets, broadcasters, digital, our, american, cea, million, receive, sales, projections" />
	<meta name="description" content="For those of you who have studied the HDTV movement the name Gary Shapiro will be familiar. He had headed the Consumer Electronics Association in Washington D.C. since the inception of the H/DTV movement. It is his big issue on his watch. Along the way he has publicly bumped heads with broadcast and cable and is doing so again with his attempts to get an uncertain date for the cut off of analog broadcasting legally fixed to a specific &quot;date certain.&quot; He is seeking legislation that will terminate the anlog broadcast on a specific date and calculates that the pain that may come from making that date December 31, 2007 is tolerable. The following letter from Gary was sent to Congress just yesterday. It is brought to you in its entirety. With this letter and the speech to the ATSC by &lt;a href=&quot;http://www.hdtvmagazine.com/articles/articles-author.php?id=5&quot;&gt;Eddie Fritts&lt;/a&gt;, CEO of the National Association of Broadcasters, you begin to see &quot;behind the curtain.&quot; _Dale Cripps
" />
	<meta name="title" content="Do You Want A Date Certain?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Do You Want A Date Certain?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/05/do-you-want-a-date-certain.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="For those of you who have studied the HDTV movement the name Gary Shapiro will be familiar. He had headed the Consumer Electronics Association in Washington D.C. since the inception of the H/DTV movement. It is his big issue on his watch. Along the way he has publicly bumped heads with broadcast and cable and is doing so again with his attempts to get an uncertain date for the cut off of analog broadcasting legally fixed to a specific &quot;date certain.&quot; He is seeking legislation that will terminate the anlog broadcast on a specific date and calculates that the pain that may come from making that date December 31, 2007 is tolerable. The following letter from Gary was sent to Congress just yesterday. It is brought to you in its entirety. With this letter and the speech to the ATSC by &lt;a href=&quot;http://www.hdtvmagazine.com/articles/articles-author.php?id=5&quot;&gt;Eddie Fritts&lt;/a&gt;, CEO of the National Association of Broadcasters, you begin to see &quot;behind the curtain.&quot; _Dale Cripps
" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=6', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/05/do-you-want-a-date-certain.php">Do You Want A Date Certain?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 12, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=4&category=Politics & Policy">Politics & Policy</a></b>
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
				<p>For those of you who have studied the HDTV movement the name Gary Shapiro will be familiar. He had headed the Consumer Electronics Association in Washington D.C. since the inception of the H/DTV movement. It is his big issue on his watch. Along the way he has publicly bumped heads with broadcast and cable and is doing so again with his attempts to get an uncertain date for the cut off of analog broadcasting legally fixed to a specific "date certain." He is seeking legislation that will terminate the anlog broadcast on a specific date and calculates that the pain that may come from making that date December 31, 2007 is tolerable. The following letter from Gary was sent to Congress just yesterday. It is brought to you in its entirety. With this letter and the speech to the ATSC by <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>, CEO of the National Association of Broadcasters, you begin to see "behind the curtain." _Dale Cripps</p>

<p></p>

<p>Dear Chairman Barton,</p>

<p>Thank you so much for your strong leadership on the digital television (DTV) transition. We believe you are doing the right thing for our nation in forcing the issue of a hard cut off date for analog broadcasting.</p>

<p>Both the debate and the actual setting of a firm date will have strong positive effects of moving this process along, publicizing the DTV transition, and freeing up the analog spectrum for more significant public safety and new technology uses.</p>

<p>We have provided some actual and projected data to assist your efforts.<br />
Homes Relying Exclusively on Analog Over the Air Signals</p>

<p><strong>In essence, the most important issue in cutting off of analog service is: who will be disenfranchised?</strong> <strong>Or, how many American homes will not receive a television signal on the cutoff date?<br />
</strong><br />
The fact is that the percentage of American homes relying only on an over the air signal is low and shrinking. While the vast majority of Americans receive local and network feeds via cable and satellite (and soon via telephone line, cellular, wireless broadband and the Internet), relatively few rely exclusively on a free over the air antenna signal.</p>

<p>If there is any doubt about this, consider the total lack of public outcry over the recent announcement that Monday Night Football will be soon available only to satellite and cable households!</p>

<p>Of the nearly 110 million American homes with at least one TV, 68% receive a cable signal and 22% receive a DBS signal. Our research shows that roughly 3% receive both cable and DBS. In total, 87% of American homes will have access to cable or satellite (and thus network and local feeds).</p>

<p>This means that if the cut off occurred today, less than 13% of the population of 110 million TV households would not have access to a broadcast signal through cable or satellite (though they could certainly start subscribing).</p>

<p>And this number is shrinking every year. Cable and satellite penetration continues to grow about one to two percentage points annually. Indeed, yesterday Sanford Bernstein said recent data suggest subscribers are growing 3.6 percent annually!</p>

<p>More, the number of non-subscribers only homes may be less relevant as broadband penetration grows. Broadcasters are increasingly providing their content through other means including the Internet and through cell phones. Just recently, Verizon announced that a deal where it would provide NBC's feed over its fiber network.</p>

<p>By the time of the actual cut-off -- combining present adoption trends for cable and satellite and forecasts for uptake of recently announced TV services from telcos like Verizon and SBC, as well as the change in purchasing likely to occur with a hard cut off date - the number of American homes which would be cut off from any broadcast signal would be significantly less than 13%.<br />
With respect to the people who have neither cable nor satellite, our research shows that this population's decision not to subscribe is generally not made for economic reasons. Instead, these are primarily people who do not watch a great deal of TV.</p>

<p>Those who do not subscribe to cable or satellite watch, on average 30% less television per week than cable and satellite subscribers. Nearly six of ten say television simply is not a high priority for them. Fewer than 30% indicate that insufficient funds play a role in their decisions not to subscribe to television.</p>

<p>But we must acknowledge that a small portion of the population will be adversely affected by an analog cut off. That is why we respect and understand your interest in creating a program whereby these viewers would have access to low cost digital-to analog converters.<br />
However, given the rapid growth of alternative forms of media delivery, a government effort to ensure that every American has some type of service after the analog cut off will not be as widespread a challenge as some people believe.</p>

<p>Homes with Satellite or Cable Broadcasters seek to make a large issue out of the unconnected analog TV sets in households that subscribe to satellite or cable TV. Broadcasters would have you believe that these sets are used extensively with antennas for watching over the air analog signals. In fact, primary viewing most often occurs on the TV that is connected to pay services. More often, the disconnected TVs are shunted to a less used room and hooked up with a DVD, VCR, or video game player. Indeed, our research shows these sets are used at least half the time for one of many alternate uses. More, as cable companies no longer have a monthly charge for additional outlets, this issue is irrelevant for the 68% of cable homes. In any event, with the analog cut off, these homes will not be disenfranchised, rather, they will simply purchase a D to A converter to continue receiving a broadcast signal, assuming they choose to do so.<br />
Homes with Digital Televisions<br />
Digital television has been adopted twice as quickly as color television. It took color television ten years to achieve 5% penetration from introduction; digital television products are already in 16 million American homes!</p>

<p>Broadcasters focus on the fact that most of these sets are not receiving an over the air signal. The fact is that the majority of these sets are hooked up to cable or satellite where increasingly, the signal is digital. Indeed, 3.5 million homes already have a television set with an integrated tuner or use a set top box to receive an ATSC signal. (Every DBS HD box also has an off-air DTV/HD tuner and is part of this calculation). We estimate that an additional 1.5 million homes are cable households who can receive DTV/HD broadcasts via cable. This means today that some five million American homes receive a DTV signal.</p>

<p>This estimate is consistent with our projections made in 1997 when Congress passed the law focusing on the 2006 deadlines. That same day in 1997 CEA issued a press release projecting that DTV penetration would only be 30% by the deadline. We recently had to scale back our overly optimistic DTV sales projections for 2005. We had based those projections on early FCC action on the tuner mandate petition and extensive promotion of cablecards, neither of which came to pass. We believe that 2005 DTV product sales will actually jump to 14.8 million units from 7.1 million units in 2004.</p>

<p>Future Sales Projections</p>

<p>To assist your focus on a hard cut off date, we are providing integrated DTV shipment estimates (meaning those sets with an integrated DTV tuner) for the next few years based on certain assumptions. Future projections are difficult as they are based on consumers making buying decisions which are affected by a range of factors we can't control, such as the economy, programming options, manufacturer offerings and government action. These forecasts assume the following:</p>

<p><strong><em>1. Congress will Pass in 2005 a Hard cut-off Date for Analog Broadcasting. </em></strong></p>

<p>This is the most important factor, as setting any reasonable date will allow consumers to focus on the inevitable, allow manufacturers to include a warning label for analog sets that is tied to a clear date (manufacturers cannot do that now as the cut off dates are unclear). Further, this will allow strong publicity about the inevitability of the transition.</p>

<p><em><strong>2. The FCC will Act Quickly on the CEA Petition to Eliminate the 50% Schedule and Accelerate the 100% Schedule to March 2006 on Mid-Size Television Sets (25 inches to 36 inches). </strong></em></p>

<p>Again, we recently scaled back our overly optimistic DTV sales projections for 2005. Our initial projections were based in part on early FCC action on the tuner mandate petition and more aggressive promotion and sales of cable card which in turn would drive sales of digital cable ready sets (which contain a tuner as dictated by the standard). Fewer sets with tuners will be sold if the FCC fails to act quickly on the CEA petition to remove the 50% rule OR imposes an earlier deadline for the inclusion of DTV tuners sets. Manufacturers have production cycles and respond to market demands and consumers and retailers are not demanding integrated sets.<br />
3. National Groups for Local Broadcasters Will Continue to Do Little or Nothing to Promote Free Over the Air Digital Television. CEA has spent millions of dollars participating in home shows, with traveling media spokesmen, on brochures, on awards programs, and on creating and updating a website focused exclusively on helping people buy over the air antennas. Our antenna promotion website, www.antennaweb.org, receives 200,000 hits per month and promotes over the air broadcasting. Consumers face many choices and the free market decision to buy a more expensive TV with a digital tuner is a difficult one when broadcasters are quiet and consumers can get the service they want over cable or satellite. If broadcasters devote their considerable muscle to promoting free over the air digital television, then we would expect to see greater consumer interest in and sales of digital televisions with over the air tuners.</p>

<p><a href="http://www.ce.org/shared_files/pr_attachments/20050511barton_release.doc">Please see attachment for specific data</a></p>

<p><em><strong>Conclusion<br />
</strong></em>An analog transmission cut off is important for our nation. But, it will have little practical impact on the viewing habits of the vast majority of Americans. As 87% of American homes do not rely on over the air signals for broadcast content, the impact and even the need for televisions with tuners is increasingly questionable.<br />
Indeed, it is remarkable that the organizations of local broadcasters seeking to delay an analog cut off are the same organizations that have refused to educate the public on the existence, much less the value, of free over the air service. In short, while PBS and some local broadcasters have been exemplary in their educational efforts, the national broadcaster organizations have done almost nothing to promote to the public the value of free over the air broadcasting. Instead, they have used a "Washington only" strategy of delaying the cut off date and seeking restrictions on cable, satellite and TV set makers (and now they are going after telephone companies who provide video signals).</p>

<p>The public, not the broadcasters, owns this spectrum. The broadcasters now have twice the spectrum they were originally loaned, and they are clearly unwilling to give it up. The country needs this spectrum, and the 13% (see above) of American homes today (and fewer each year) who rely solely on free over the air broadcasting will understand that they have alternatives once a hard cut off date is set.</p>

<p>The needs of the many are too great, the spectrum is way under-utilized, and the stakes are high. We urge you to stay the course and impose a hard deadline and we stand ready to help you in this noble cause."</p>

<p>About CEA: The Consumer Electronics Association (CEA) is the preeminent trade association promoting growth in the consumer technology industry through technology policy, events, research, promotion and the fostering of business and strategic relationships. CEA represents more than 2,000 corporate members involved in the design, development, manufacturing, distribution and integration of audio, video, mobile electronics, wireless and landline communications, information technology, home networking, multimedia and accessory products, as well as related services that are sold through consumer channels. Combined, CEA's members account for more than $121 billion in annual sales. CEA's resources are available online at www.CE.org, the definitive source for information about the consumer electronics industry. CEA also sponsors and manages the International CES - Defining Tomorrow's Technology. All profits from CES are reinvested into industry services, including technical training and education, industry promotion, engineering standards development, market research and legislative advocacy.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 12, 2005 10:37 AM</b>
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
			<?=getComments(6)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 6)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/do-you-want-a-date-certain.php" type="text/javascript" charset="utf-8"></script>
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