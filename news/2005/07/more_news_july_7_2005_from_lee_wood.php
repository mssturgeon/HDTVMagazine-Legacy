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
		AND e.entry_id = 142";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Lee Wood" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 142 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Lee Wood'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Lee Wood" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 142 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 142";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 142";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download More News July 7, 2005 From Lee Wood" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="More News July 7, 2005 From Lee Wood" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="More News July 7, 2005 From Lee Wood" />
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
	<title>HDTV Magazine - More News July 7, 2005 From Lee Wood</title>
	<meta name="keywords" content="engineering broadcastengineering, broadcast engineering, broadcastengineering newsletters, technology tvtechnology, tvtechnology dlrf, news, digital, broadcast, dtv, engineering, new, article, technology, broadcastengineering, newsletters, display, television, cable, july, tech, top, set, consumer, tvtechnology, channel" />
	<meta name="description" content="DTV Station Status Per FCC CDBS - July 5, 2005(TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=928 Senate Skeds 2 DTV Hearings [Paid Subscription Required] The Senate Commerce Committee will hold not one but two full-committee hearings on the DTV transition July 12 -- at..." />
	<meta name="title" content="More News July 7, 2005 From Lee Wood" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="More News July 7, 2005 From Lee Wood" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="DTV Station Status Per FCC CDBS - July 5, 2005(TV Technology) http://www.tvtechnology.com/dlrf/one.php?id=928 Senate Skeds 2 DTV Hearings [Paid Subscription Required] The Senate Commerce Committee will hold not one but two full-committee hearings on the DTV transition July 12 -- at..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=142', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php">More News July 7, 2005 From Lee Wood</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Lee Wood</b> on <b>July  7, 2005</b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><strong>DTV Station Status Per FCC CDBS - July 5, 2005</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dlrf/one.php?id=928">http://www.tvtechnology.com/dlrf/one.php?id=928</a> </p>

<p><strong>Senate Skeds 2 DTV Hearings  </strong>[Paid Subscription Required]<br />
The Senate Commerce Committee will hold not one but two full-committee hearings on the DTV transition July 12 -- at 10 a.m. and 2:30 p.m in room 253 of the Russell Building, for those marking their calendars.<br />
(Broadcasting & Cable)<br />
<a href="http://www.broadcastingcable.com/article/CA623242?display=Breaking+News">http://www.broadcastingcable.com/article/CA623242?display=Breaking+News</a> </p>

<p><strong>Commission to Revisit Must Carry</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dailynews/one.php?id=3070">http://www.tvtechnology.com/dailynews/one.php?id=3070</a> </p>

<p><strong>Martin Backs Into Dual 'Must'  </strong>[Paid Subscription Required]<br />
Plan Would Allow B'casters to Opt for Retrans Consent on a Second Signal<br />
(Multichannel News)<br />
<a href="http://www.multichannel.com/article/CA623069.html?display=Top+Stories">http://www.multichannel.com/article/CA623069.html?display=Top+Stories</a> <br />
 <br />
<strong>BIA Financial analyzes DTV channel election aftermath</strong>A review of the first round DTV channel elections shows that 246 stations have not received a tentative post-transition DTV channel assignment. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#bia">http://broadcastengineering.com/newsletters/hd_tech/20050706/#bia</a><br />
 <br />
<strong>DTV legislation has 80 percent chance of passage by Congress</strong>Congress appears committed to passing legislation by the end of the year to set a date for analog broadcasts to cease. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/bth/20050704/#congress">http://broadcastengineering.com/newsletters/bth/20050704/#congress</a> </p>

<p><strong>Broadcast flag dispute continues</strong>An amendment authorizing federal regulators to mandate the broadcast flag has not been proposed, as many thought it might, leaving the issue to hang in the breeze. <br />
(Broadcast Engineering)<br />
<a href="http://www.broadcastengineering.com/404/#flag">http://www.broadcastengineering.com/404/#flag</a> </p>

<p><strong>Surviving the Digital TV Shift </strong>Soon, TV stations will give up their old analog licenses and broadcast solely in digital format. This means older TV sets will no longer receive broadcast signals. Here's how you can prepare for the big changeover.<br />
(Wired News)<br />
<a href="http://www.wired.com/news/digiwood/0,1412,68091,00.html?tw=rss.TEK">http://www.wired.com/news/digiwood/0,1412,68091,00.html?tw=rss.TEK</a> </p>

<p><strong>Winegard Exec: Education the Key to Successful DTV Transition</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dlrf/one.php?id=927">http://www.tvtechnology.com/dlrf/one.php?id=927</a> </p>

<p><strong>Defining Visions: Consumer Beware</strong>  [Joel Brinkley]<br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/joelbrinkley/705jb/">http://www.guidetohometheater.com/joelbrinkley/705jb/</a> </p>

<p><strong>Consumer Group Refutes CEA Findings on OTA TV Viewing</strong>(TV Technology)<br />
<a href="http://www.tvtechnology.com/dlrf/one.php?id=921">http://www.tvtechnology.com/dlrf/one.php?id=921</a> </p>

<p><strong>Study says 80 million OTA TVs still in use</strong><br />
Results of a survey conducted by Consumers Union and the Consumer Federation of America indicate 15 percent of TVs are used exclusively to receive OTA television transmissions. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#">http://broadcastengineering.com/newsletters/hd_tech/20050706/#</a> </p>

<p><strong>HD capabilities desired by majority of TV buyers</strong>, says survey<br />
The finding is part of a new report that explores consumer attitudes about different aspects of TV installation and usages. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#buyers">http://broadcastengineering.com/newsletters/hd_tech/20050706/#buyers</a><br />
 <br />
<strong>July 1 DTV Update</strong>(Digital Television)<br />
<a href="http://digitaltelevision.com/articles/article_969.shtml">http://digitaltelevision.com/articles/article_969.shtml</a><br />
 <br />
<strong>ATSC Tuner Mandate Takes Effect</strong><br />
(TV Technology)<br />
<a href="http://">http://</a>http://www.tvtechnology.com/dlrf/one.php?id=922<br />
 <br />
<strong>20050627 Mark's Monday Memo</strong>(Digital Television)<br />
<a href="http://www.digitaltelevision.com/mondaymemo/mlist/frm02189.html">http://www.digitaltelevision.com/mondaymemo/mlist/frm02189.html</a> </p>

<p><strong>WGNM-TV expands service with new digital transmitter</strong>WGNM-TV announces the launch of its new digital transmitter on Channel 45.<br />
(Macon, GA Telegraph)<br />
<a href="http://www.macon.com/mld/macon/business/12038166.htm">http://www.macon.com/mld/macon/business/12038166.htm</a> </p>

<p><strong>FCC Sets Set-Top Check-Up Deadline  </strong>[Paid Subscription Required]<br />
The top six cable operators and the leading cable industry and consumer electronics trade groups must begin filing status reports with the FCC on Oct. 1 detailing their progress toward a new deadline for eliminating cable set-top boxes that combine anti-theft security with channel surfing and interactive functions.<br />
(Broadcasting & Cable)<br />
<a href="http://www.broadcastingcable.com/article/CA623348?display=Breaking+News">http://www.broadcastingcable.com/article/CA623348?display=Breaking+News</a> </p>

<p><strong>Charter, Advance/Newhouse Sue FCC</strong>Charter Communications and Advance/Newhouse Communications went to federal court Tuesday to overturn set-top-box rules adopted in March by the FCC.<br />
(Multichannel News)<br />
<a href="http://www.multichannel.com/article/CA623832.html?display=Breaking+News&referral=SUPP">http://www.multichannel.com/article/CA623832.html?display=Breaking+News&referral=SUPP</a> </p>

<p><strong>High Def-inn-ition  </strong>[Paid Subscription Required]<br />
The new luxury hotel amenity: HD on a flat-screen TV<br />
(Broadcasting & Cable)<br />
<a href="http://www.broadcastingcable.com/article/CA623030.html?display=Technology">http://www.broadcastingcable.com/article/CA623030.html?display=Technology</a></p>

<p><strong>Moore's Law Meets HDTV </strong>(Display Technology Investor via Forbes)<br />
<a href="http://www.forbes.com/investmentnewsletters/2005/07/01/sony-brillian-hitachi-lcos-spatialight-cx_dm_0701soapbox_inl.html?partner=rss">http://www.forbes.com/investmentnewsletters/2005/07/01/sony-brillian-hitachi-lcos-spatialight-cx_dm_0701soapbox_inl.html?partner=rss</a> </p>

<p><strong>Digital Terrestrial TV Set Tops Ready For Blast Off</strong>(In-Stat)<br />
<a href="http://www.in-stat.com/press.asp?ID=1390&sku=IN0501846ME">http://www.in-stat.com/press.asp?ID=1390&sku=IN0501846ME</a> <br />
 <br />
<strong>NAB, MSTV issue converter box RFQ</strong>The associations have issued a request for quote on a prototype digital-to-analog converter box. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#rfq">http://broadcastengineering.com/newsletters/hd_tech/20050706/#rfq</a> </p>

<p><strong>MSTV, NAB seek proposals for DTV-to-NTSC converter box</strong>The consumer device would enable DTV viewing on analog receivers. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/t2d/20050705/#mstv">http://broadcastengineering.com/newsletters/t2d/20050705/#mstv</a> </p>

<p><strong>Emerging force in large LCD televisions </strong>FOR years, China has been one of the world's largest manufacturers of electrical and electronic goods. For the longest time, however, people have shun away from buying made-in-China products unless they are under the guise of and guaranteed by leading international brands.<br />
(New Straits Times)<br />
<a href="http://www.nst.com.my/Current_News/NST/Wednesday/Features/20050705150929/Article/indexb_html">http://www.nst.com.my/Current_News/NST/Wednesday/Features/20050705150929/Article/indexb_html</a> </p>

<p><strong>Help, I Need a New HDTV! (Part 1 of 5)</strong>  [Includes Links to All 5 Parts]<br />
(Architechtronics via Self SEO)<br />
<strong>http://www.selfseo.com/story-3400.php</strong> </p>

<p><strong>Hitachi 50VX915 Directors Series LCD RPTV</strong><br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/directviewandptvtelevisions/705hitachi/">http://www.guidetohometheater.com/directviewandptvtelevisions/705hitachi/</a> </p>

<p><strong>Mitsubishi Wins the Race</strong>The Japanese giant is the first to bring a 1080p DLP TV to market.<br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/news/070105Mitsubishi/">http://www.guidetohometheater.com/news/070105Mitsubishi/</a> <br />
 <br />
<strong>NEC MultiSync LCD2335WXM</strong>The NEC MultiSync LCD2335WXM is an admirable PC display that falls short as an HDTV.<br />
(PC Magazine)<br />
<a href="http://www.pcmag.com/article2/0,1759,1833400,00.asp?kc=PCRSS02129TX1K0000530">http://www.pcmag.com/article2/0,1759,1833400,00.asp?kc=PCRSS02129TX1K0000530</a><br />
 <br />
<strong>Pioneering</strong>Pioneer launches their sixth generation of plasma displays and two intriguing new AV receivers.<br />
(Ultimate AV)<br />
<a href="http://www.guidetohometheater.com/news/070405Pioneer/">http://www.guidetohometheater.com/news/070405Pioneer/</a> </p>

<p><strong>Yesterday"s Technology Provides a Clear Solution to a Modern Problem</strong><br />
Just a few short years ago, you would have been hard-pressed to find a rooftop television antenna on an afternoon drive through Middle America. But with the growing popularity of high-definition television (HDTV), antennas are making a comeback in homes across the country.<br />
(PRWeb via Yahoo News)<br />
<a href="http://news.yahoo.com/news?tmpl=story&u=/prweb/20050703/bs_prweb/prweb257605_1">http://news.yahoo.com/news?tmpl=story&u=/prweb/20050703/bs_prweb/prweb257605_1</a><br />
 <br />
<strong>MPEG-4 AVC H.264 to usher in new HD opportunities</strong><br />
Emerging products of tomorrow will be enabled by MPEG-4 AVC H.264 -- a technology that one industry expert says will drive the industry on the road to the future. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#oppt">http://broadcastengineering.com/newsletters/hd_tech/20050706/#oppt</a> <br />
 <br />
<strong>DVB-T/USB chip drives digital TV reception for PCs</strong>(Electronic Engineering Times-Asia)<br />
<a href="http://www.eetasia.com/ART_8800370566_499489_896c0835_no.HTM">http://www.eetasia.com/ART_8800370566_499489_896c0835_no.HTM</a> </p>

<p><strong>3G Americas publishes Mobile TV paper</strong>(3G Newsroom)<br />
<a href="http://www.3gnewsroom.com/3g_news/jul_05/news_6031.shtml">http://www.3gnewsroom.com/3g_news/jul_05/news_6031.shtml</a> </p>

<p><strong>Crown Castle fixing to build a nationwide digital TV network for cellphones</strong>(Engadget)<br />
<a href="http://www.engadget.com/entry/1234000817049511/">http://www.engadget.com/entry/1234000817049511/</a> </p>

<p><strong>Abertis Telecom, Nokia and Telefonica Moviles to Start First Digital Mobile TV (DVB-H) Pilot in Spain </strong> [Spain]<br />
Live Broadcast Programs Will be Provided by Antena 3, Sogecable, Telemadrid, Telecinco, TVE and TV3<br />
(PR Newswire)<br />
<a href="http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/07-04-2005/0004051687&EDATE=">http://www.prnewswire.com/cgi-bin/stories.pl?ACCT=104&STORY=/www/story/07-04-2005/0004051687&EDATE=</a> </p>

<p><strong>Shaw Boosts its High Definition (HDTV) Lineup  [Canada]</strong><br />
(Digital Home)<br />
<a href="http://digitalhomecanada.com/index.php?option=com_content&task=view&id=475&Itemid=51">http://digitalhomecanada.com/index.php?option=com_content&task=view&id=475&Itemid=51</a> </p>

<p><strong> IT, HD drive growth in European broadcast market</strong>, says report<br />
Over the next five years, European broadcasters will begin HD transmission and require the hardware and software to make the transition, according to a new report. <br />
(Broadcast Engineering)<br />
<a href="http://broadcastengineering.com/newsletters/hd_tech/20050706/#europe">http://broadcastengineering.com/newsletters/hd_tech/20050706/#europe</a></p>

<p><strong>Who'll be the big winner in 2012? </strong> [UK]<br />
Digital TV providers face a race against time to sign up subscribers ahead of the analogue switch off.<br />
(Guardian Unlimited)<br />
<a href="http://www.guardian.co.uk/online/news/0,12597,1520972,00.html">http://www.guardian.co.uk/online/news/0,12597,1520972,00.html</a> </p>

<p><strong>Freeview tipped to top Sky at shutdown  </strong>[UK]<br />
(Digital Spy)<br />
<a href="http://www.digitalspy.co.uk/article/ds22318.html">http://www.digitalspy.co.uk/article/ds22318.html</a> </p>

<p><strong>Industry puts Freeview in pole position  </strong>[UK]<br />
(Digital TV Group)<br />
<a href="http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=986">http://griffin.dtg.org.uk/news/news.php?class=countries&subclass=193&id=986</a> <br />
 <br />
<strong>Sky's HD service to include Premiership games  </strong>[UK]<br />
(Digital TV Group)<br />
<a href="http://griffin.dtg.org.uk/news/news.php?">http://griffin.dtg.org.uk/news/news.php?</a>class=countries&subclass=193&id=988<br />
 <br />
<strong>French Deputy supports EuroNews DTT candidacy  </strong>[France]<br />
(Advanced Television)<br />
<a href="http://www.advanced-television.com/2005/news_archive_2005/July4_July8.htm#frenchd">http://www.advanced-television.com/2005/news_archive_2005/July4_July8.htm#frenchd</a> <br />
 <br />
<strong>TV on PC</strong>  [Australia]<br />
Can your computer tune in digital shows, record them or pause live video?<br />
(Sydney Morning Herald)<br />
<a href="http://www.smh.com.au/news/icon/tv-on-pc/2005/06/30/1119724747965.html?oneclick=true">http://www.smh.com.au/news/icon/tv-on-pc/2005/06/30/1119724747965.html?oneclick=true</a> </p>

<p><strong>Setting up for set-tops  </strong>[Australia]<br />
(PC World)<br />
<a href="http://www.pcworld.idg.com.au/index.php/id;1202732134;fp;16;fpid;0">http://www.pcworld.idg.com.au/index.php/id;1202732134;fp;16;fpid;0</a> </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Lee Wood</b>, <b>July  7, 2005  8:42 AM</b>
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
			<?=getComments(142)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Lee Wood', 142)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Lee Wood</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2005/07/more-news-july-7-2005-from-lee-wood.php" type="text/javascript" charset="utf-8"></script>
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