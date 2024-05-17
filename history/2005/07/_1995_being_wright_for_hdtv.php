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
		AND e.entry_id = 149";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 149 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 149 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 149";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/07/_1995_being_wright_for_hdtv.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (5) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 149";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download  1995 -- Being Wright For HDTV" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content=" 1995 -- Being Wright For HDTV" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content=" 1995 -- Being Wright For HDTV" />
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
	<title>HDTV Magazine -  1995 -- Being Wright For HDTV</title>
	<meta name="keywords" content="grand alliance, dale cripps, best picture, robert wright, same time, hdtv, new, spectrum, digital, nbc, business, cable, standard, programs, wright, broadcasters, broadcast, fcc, big, could, broadcasting, signals, channel, programming, network" />
	<meta name="description" content="Radio spectrum is the chief asset of the American broadcast system. As long as available it has been granted free to qualified applicants in the interest of public service. Without radio spectrum the broadcast business would disappear. The more spectrum broadcasters can have, the better it is... for them. That view must be kept in mind when divining the meaning behind statements touching upon radio spectrum. 
" />
	<meta name="title" content=" 1995 -- Being Wright For HDTV" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content=" 1995 -- Being Wright For HDTV" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/07/_1995_being_wright_for_hdtv.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Radio spectrum is the chief asset of the American broadcast system. As long as available it has been granted free to qualified applicants in the interest of public service. Without radio spectrum the broadcast business would disappear. The more spectrum broadcasters can have, the better it is... for them. That view must be kept in mind when divining the meaning behind statements touching upon radio spectrum. 
" />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Archive &amp; History Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=149', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/07/_1995_being_wright_for_hdtv.php"> 1995 -- Being Wright For HDTV</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>July  8, 2005</b>
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
			<div id="<?=$container?>" class="<?=$viglink_suppress?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><em>It's now ten years since this article was published in various magazines. In it you will find some of the rich mechanizations which occurred prior to the standard being set and the commercialization activities begun. For those who take HDTV for granted this article is one of many that will give you a glimps at just how difficult a process it was to get clear picture to your home. There were powerful advocates, of course, but so were there powerful detractors.</em> _Dale Cripps, 2005</p>

<p><br />
<strong>"I Don't Think We Have A Business Unless We Have the Best Picture."</strong>--Robert Wright, President, NBC</p>

<p>By Dale Cripps <br />
1995</p>

<p><br />
Has the HDTV business heard the starting bell at last? At first blush it appears so. In April 1995 the president of NBC announced that the peacock network would begin broadcasting digital HDTV programs in 1997. Can others be far behind? Should we start saving our pennies and cashing in our 401s for a Christmas HDTV set? Or, is this announcement more vapor-speak?</p>

<p>Radio spectrum is the chief asset of the American broadcast system. As long as available it has been granted free to qualified applicants in the interest of public service. Without radio spectrum the broadcast business would disappear. The more spectrum broadcasters can have, the better it is... for them. That view must be kept in mind when divining the meaning behind statements touching upon radio spectrum. </p>

<p><br />
Broadcasting is today by far the most efficient means for sending "information" to the American public. TV signals reach more households than do telephones, and certainly more household than do cable connections. In such a gargantuan and influential business the big bucks are paid to those who are right more often than they are wrong. One big bucks executive is none other than Mr. Wright--Robert Wright, the president of NBC. Wright never shrinks from an opportunity to increase shareholder values. He has propelled the TV arm of GE well beyond the familiar entertainment and news network we all know. In the last ten years he fashioned two successful cable channels, acquired interest in 19 other cable services, launched NBC Super Channel in Europe, the NBC Data Net, and a Spanish language news channel in Latin America.</p>

<p>With fanfare and considerable surprise, Robert Wright dashed to the leading edge of technology as he came out strongly for HDTV. He announced in April of this year that his network would be ready to launch the broadcasting of HDTV programs and signals in 1997. Doing so will mark indelibly the day and hour when the commercialization phase of the HDTV begins. Others must follow such an important lead to remain competitive or face a future in an AM radio-type status. But many ask why NBC would decide to do this? HDTV has never looked like a bonanza, or even a business to the existing signal providers. Wright begins his answer, "It is inconceivable to me that broadcasters, of all people, would want to be in any way left out of complete parity in high definition. Unlike the satellite operators--and we are both--the broadcaster is paranoid about having anything but the best picture on the air. I don't think we have a business if we end up with less than the best picture."</p>

<p><strong>Things Constantly Change In Broadcasting</strong><br />
Like no other, the business of broadcasting is always in full public view. The Connie Chung dismissal illustrates this point only mildly. It is a shareholder owned entity thriving or thrashing from both government and public perceptions, tastes, and opinions. Broadcasters must frequently shift gracefully from one political position to another while appearing fully confident and consistent in serving all parties equally. Both defense and offense are the tools of the trade in combating innumerable threats which surface daily. One moment the threat is a hot program competitor, the next a whiz bang revolutionary technology from left field, another might be a curve ball wielded by government regulators. As a result, the managers of network television have become sublimely nimble in choosing advance or retreat, offense or defense as tools of their trade. These survivors have grown extremely skillful and are fully sensitized to seizing major opportunities, the likes that the lesser-experienced fail to recognize. That's why they get the big bucks.</p>

<p><strong>NBC Was Always A Player</strong><br />
As it did with color, NBC has played a significant role in advanced television in the US. Upon learning in 1987 that broadcasters needed to act on the HDTV question, the peacock network teamed with its world renowned R&D arm--the David Sarnoff Research Center in Princeton, New Jersey. They set out to find both a business model for HDTV and its most fitting technical solution--one that adequately responded to the "foreign led threat".</p>

<p>NBC's first of many differing positions on HDTV was to agree quickly with their adversary and become an advocate for HDTV by way of a "reasonable" industry proposal. NBC backed a compatible EDTV (Extended Definition of the old standard) solution, one that could be cheaply added to the broadcast plant and still evolve to HDTV later when displays were bigger, clearer, and cheaper. They reasoned that the compatible approach, while a forced compromise, was the only business decision that could honestly be made. But a FCC decree (initiated by George Bush) in 1991 demanded a departure from the old NTSC standard, making compatibility out-of-the-question. NBC abandoned quickly their compatible system while at the same time praising the FCC decision as a stroke of genius. That same day they allied their strength in a tighter pact with Thomson and Philips in an all-digital non-compatible approach.</p>

<p>A new world-beating digital standard (submitted by the Grand Alliance --Philips, Thomson, General Instrument, Zenith, AT&T, and MIT) is the end result of the eight years of work since the FCC began the process. As noted in earlier editions of Widescreen Review, the Grand Alliance system can do more than decode HDTV signals. It permits a high degree of flexibility, i.e., it can allocate varying amounts of bits per second to represent the image of a program(s), or send data services. Choosing anything less than the peak HDTV requirement of 18 Mbit/s would allow for more programs and data on the same 6 MHz channel. At least four programs with the current NTSC image quality can be squeezed into just one 6 MHz channel using the new system. Either decoder boxes or new digital sets (anything from standard resolution to HDTV) can decode the signals for display (or recording on the new digital VCR). Later on with improved encoding there could be even more programs per channel. This has been a somewhat intriguing alternative to broadcasters--one which is more than a replacement of one old NTSC channel for one new HDTV channel. This flexibility has decidedly enhanced strategies to compete with cable by delivering more over-the-air choices in a given region. Imagine a community where there are five standard channels. In the multiplex digital world those five could deliver to their community 20 or more separate ghost-free, noise free, programs, and with either more local content or cable-like programs, or premium pay movie services--enough to dissuade many from hooking up to their local cable or telephone TV program providers and turning back to everything offered from over-the-air.</p>

<p>Cable doesn't like this idea and wants broadcasters to be forced by the government to deliver full bodied HDTV in the new channel. But this imposes a dilemma on cable as well since the 'must carry' rules makes them carry the bandwidth intensive HD programs of all the local broadcasters at the expense of their own valuable spectrum.</p>

<p>Lobbying efforts in Washington by the National Association of Broadcasters had intensified over the last two years. They were asking for Congressional blessings in a flexible use approach for the new spectrum. That left HDTV appearing again little more than a side show in a completely changing all-digital era. This request for flexible use was heard in Congress and is written into both the Senate and House versions of the 1995 Telecommunications Act.</p>

<p><strong>Manufacturers Confused</strong><br />
Long suffering manufacturers had hoped that the FCC, or better Congress, would mandate the use of the new conversion channel for at least some HDTV programming. That mandate looks to be lost in this Telecommunications Act of 1995, if it passes. The manufacturers have never seen eye to eye with the broadcasters believing they were authors of numerous delays designed to slaughter the whole movement. Their first grievous concern was that the NBC compatible approach would scuttle, or, at a minimum, slow the market appeal of advanced TV due to quality compromises. On the other hand since NBC was a major network involved as a system proponent they were eager to believe that no matter what was being said or done, the lead taken by NBC meant that broadcasters were really behind the HDTV movement. That was bolstered by the fact that tens of thousands of hours of voluntary committee work had been contributed by broadcasters, cable, and satellite participants. It would, therefore, end in a great new business in America once a standard was set by the FCC. But NBC too withdrew from the FCC process when the Grand Alliance was formed in the spring of 1993, leaving an uneasiness among those same manufacturers and most of the industry observers. Committee work drew to a close and participants moved on to new things. The business of HDTV looked as if it would have to find a champion outside of the usual ranks, if even that. The wind was falling out of the sails and the Electronic Industries Association HDTV/ATV committee--made up of executives from the world's giant consumer electronic manufacturers--were adrift and losing heart.</p>

<p><strong>NAB</strong><br />
Critical mass is when there is enough interest to form a commitment that in itself has the power to discard the old and introduce the new. In the field of consumer television this critical mass is made up of thousands upon thousands of people who must be motivated to act their parts out at whatever cost until the bridge is crossed. The consumer is part of this critical mass, but not yet exposed nor participating in adopting the "new." The level of response and praise that has come to the Grand Alliance most businesses would die for. But when it comes to moving into the next generation of television, a tremendous strength must be aggregated. To date this strength has not been so aggregated and it pains this writer to say that with all of the smiling faces and self-congratulatory statements being made I have to continue saying that this is not yet sufficient. --Dale Cripps</p>

<p>The Grand Alliance valiantly demonstrated their hardware and transmission system at the '95 NAB Convention in Las Vegas. While it worked extremely well, the veterans in the industry passed by the tucked away exhibits in the Hilton Hotel with little more than a casual interest and perfunctory questioning--mostly to see how digital compression might apply to saving them money for what they are already doing. A brief fire of enthusiasm lit up when news of Wright's statements (carried to the NAB by NBC VP of Engineering, Mike Sherlock) wafted around the hallways. That faded into a disbelief, endemic to the general cynicism that has hounded HDTV over the last five years. The AMSTV's board breakfast, where a full endorsement of HDTV transmission was officially given by its several hundred broadcast members, was another big boost. But again, this lift seemed short lived due to the same heavy cynical attitude supported mostly by smaller broadcast and cable operators who feared the expense.</p>

<p>Even with the hot demonstrations, the industry endorsements, and a general underlying feeling that in spite of everything HDTV was coming along, no jubilation occurred among the developers. They too suspected that a public card was being played for spectrum rather than a sincere expression of intent to engage HDTV. Eager antenna and transmission manufacturers also talked it up and gave impressive demonstrations, but they were discounted as but eager vendors. The neophytes to HDTV ogled it, but acknowledged it was just "too much for them" to handle. It was still something for the big guys, or a new big guy who collected other big guys to the task of launching the next generation of moving imagery.</p>

<p>The best of all news was that HDTV production equipment looked to be cheaper. JVC was showing their new 1 million pixel 3/4" CCD camera for plus or minus $65,000--a far cry from the $1/2 million needed to acquire Sony's 1" CCD jewel. Also the W-VHS was demonstrated for under $10,000 for professional uses, i.e., business videos. While both of these new JVC entrants are compromises to the bandwidth of "traditional" HDTV, weep not... they look very good in comparison to studio grade NTSC.</p>

<p><strong>Never Was All Roses</strong><br />
To broadcast executives the writing had been on the wall from the very beginning. An expensive non-compatible HDTV approach looked like a turkey destined for major cooking. Non-compatible solutions would mean huge capital investments by the broadcast industry in new expensive equipment, and with no assurance whatsoever that the public would buy the new and expensive receivers to make potential any return on their investment. There hasn't been any more optimism among the three networks since it was learned they could use the new HDTV system to transmit several programs of old standard quality to digital receivers or receivers with a decoder box. Fox is a notable exception and they have championed this approach. Rupert Murdoch always hated the fact that he missed the cable opportunity in the US. Forgetting the receiver problem for the moment, he has both experience and programming from his European satellite business for dealing with multiple channel distributions. To the other networks programming is the big, big cost, so multiplexing may mean program hell. How do you continue to divide up a finite market and at the same time concentrate enough money to make high quality attractive programming? On the other hand, the non-compatible direction for advanced television provided a perfect smoke screen for which increasingly valuable spectrum might be obtained from the FCC at very little cost. That was worth pursuing by continuing with HDTV as their prime Trojan Horse.</p>

<p><strong>Critics Take Their Shots</strong><br />
With a shrinking support for HDTV clearly the case in knowledgeable circles critics like MIT Media Labs' Nicholas Negroponte seized the opportunity to get his point across. Above all Negroponte deplores any fixed HDTV standard. He favors an open device with multiple capacities able to accept scaled signals--more computer than television. In his best selling book, Being Digital, he suggests that if they are granted a license for transmitting 20 million bits of digital information every second "the very last thing a broadcaster would want do with it is to broadcast HDTV." He cites program scarcity and the limited receivers as basis for his reasoning. Negroponte also wants broadcast signals out of the airwaves. They should, in his view, be sent over wires and fiber cables and deliver variable resolution images and any number of aspect ratios to a variety of receivers--some with higher performance than others. Terrestrial spectrum, he concludes, should go to those appliances that travel. The devices which are least portable should be connected to a wire. TV set manufacturers wonder how you sell a device that does so many things. Computers outsold TV sets last year, so the answer may be clearer to those people than it is to traditional consumer electronic manufacturers.</p>

<p>In another new book --Television, Today and Tomorrow--past president of CBS, Gene Jankowski says that the American networks can't afford to enter HDTV at the sacrifice of their installed base of NTSC receivers. He points to the dismal showing in the Japanese HDTV satellite experiment and suggests that no one else can afford it either, thus reducing the competitive threat to nothing. More critically, the physical coverage from the traditional broadcast VHF band is greater than that from the poorer propagating UHF, which is the digital band. (The VHF band is to be reclaimed by the FCC following a 15 year transition period for the digital services. The FCC will reassign the reclaimed spectrum to whomsoever is in most need of it at that time, i.e., who will pay the most for it.)</p>

<p>Jankowski questions picture quality as being important to anyone. The one thing he doesn't question is the importance of original programming for creating a success in network broadcasting. He notes that American programs are dominant throughout the world and yet the production of them is in the 525 standard (used in the USA), fully one hundred lines less than the European standards of 625 scanning lines. "Despite the lower-quality picture, American programs are the standard of the world," says Jankowski, "proving that content is more important than technology." While 80% of the programs exported are produced on film, the post production for years was done using the 525 standard. The finished product was then upconverted to the 625 standards for distribution in PAL and SECAM countries. Complaints on technical quality have been heard, but to no great economic peril. With new digital recording such shortcomings have come under better control. Filmed programs are post-produced now using the 4:2:2 international digital standard, which converts to the optimum of either analog 525 or 625 standards.</p>

<p><strong>So, Why, NBC? What Does Wright See That These Others Don't?</strong><br />
The leading question is: With such negative commentary coming from high places why did Robert Wright commit NBC publicly to broadcast HDTV in 1997? In fact, Wright was not alone, just more visible. As mentioned above this year's National Association of Broadcasters Convention in Las Vegas in early April the Association of Maximum Services Telecasters--a major Washington based trade association focused to technical matters--at their annual breakfast endorsed the idea that broadcasters enter the new digital era with the commitment to broadcasting HDTV signals in prime time. This came as a surprise to many in the industry and to seasoned observers sounded too pat to be true. What was behind it? Why not promote the more economically promising multiple standard resolution channels, as had the NAB's VP John Abel? Why not kill it all-together while getting their hands on the newly reserved spectrum?</p>

<p>Columnist William Safire said in his nationally distributed column that this new spectrum for HDTV and other digital services is just too damn valuable in this day of budget contractions to be given away free. Spectrum auctioning has become the hot money making ticket in Washington. Over 7 billion dollars has been raised this year alone by the FCC auctioning off segments of it to the personal communicator and other telephone-like portable appliance companies. But auctioning of spectrum to broadcasters to enable them to compete effectively with cable and satellite HDTV services was not part of the original deal struck with the FCC in 1987. Only if the new spectrum is used for other than HDTV digital services is there any serious threat of changing the rules and ordering up what surely would be a very expensive auction. It could be, in fact, as much as a $40 billion price tag to broadcasters. (Interpreted from recent auctions of non-broadcast segments, experts contend that the broadcast spectrum could yield to the government treasury between $20 and 40 billion dollars at auction!) "It is far better to say you will do HDTV and get the spectrum assigned free. Then do as you want with it once it is in your hands. Its even OK if you actually mean what you say," came the sardonic advice from an FCC member at the NAB Convention.</p>

<p><strong>On The Other Hand...</strong><br />
Accusing Wright of using his HDTV strategy to avoid spectrum costs may be a bit harsh. They do seem to be linked topics in his talks, however, giving rise to the suspicion. He is, in essence, doing no more than coming full circle to NBC's original position on HDTV: If today's networks are to remain a high value to the American public they must be able to deliver HDTV signals and programs. "Of all the programmers (read satellite/cable, VCR/videodisk)," he said twitting Negroponte's statement above. "The broadcaster is the last one who wants to have anything but the best picture in the home." The market for "video-in-the-home" is estimated by Wright at $100 billion annually, and climbing. "Others outside of broadcasting will inevitably do HD," said the former GE executive. He doesn't want to lose ground in that big business.</p>

<p>Even if Wright's public statements have an embedded spectrum strategy contained in it, it still rings true. It will take only one successful HDTV service from cable or DBS to wake up the rest. Who should be first? This is a view consistent with all of the broadcaster's first industry-wide response to HDTV in 1987. Though the conditions for broadcasting may have dramatically changed since then, what they said in their 1987 petition to the FCC--namely that they could not afford to get caught with their pants down on the HDTV issue--is no less true today. To do that they needed a new standard and more spectrum. Still true. Competitors could deliver it without restrictive regulation or spectral scarcity. Still true--even truer considering that there is a successful DBS business going now which could easily deliver across the US perfect HDTV signals into their existing dishes from just one point in space.</p>

<p>Multiplexing is probably not such a good business idea either. Jankowski notes in his book that talent is the scarcer commodity, far more so than is spectrum or channels. If you continue to divide an audience, what choice is left but to increasingly reduce the concentration of money needed to acquire the talent to make the all-important attractive programs. You also tend to spread talent too thin, reducing the over-all-quality of the television experience. He believes there is room for no more than 6 or 7 national entertainment networks. They will produce most of the new programs and the other 500 channels, should they materialize, will acquire the afterlife syndication rights to them later. Original programming is the engine that drives any national network, and the network business is still unique and as good as free gold. Further fragmentation of audiences is more likely to affect the fragmenters' rather than the general interest audience served by the networks. Wright said about it in an April interview in Broadcasting & Cable Magazine that NBC has no business plan to exploit digital flexiblity other than to pay a little from ancillary services some of the conversion (to HDTV) costs. "I am disappointed there is so much focus on flexibility because in the end, the nature of broadcasting is such that the only way we will be able to survive is by offering all of our programming in what is the most attractive transmission and production scheme available."</p>

<p>Will these super salesman--head of the networks--earn their big bucks this time by saying anything they must to get $40 billion worth of spectrum assigned to their industry for free? Or, with enlightened self-interest will they make a preemptive HDTV strike to insure their future against any cable, telephone, or DBS ambitions... and get the spectrum fee at the same time? Or, could they actually be sincere in wanting to start the HDTV signal service as an expression of 1) gratitude for their current wealth mined from the pockets of the consumers, and 2) their good will, love, and respect for the American public... and get the spectrum free and have the preemptive strike all at the same time? I don't know which will be their reason, if any. If they hold to the third and last one, however, they get everything in the bargain, including the respect and appreciation, and perhaps even the love of their audiences.</p>

<p>We consumers are going to provide the next, and most crucial response to the HDTV question. We will undoubtedly ignore it if we feel somehow exploited by it. Or, we will jump on it like no consumer product ever introduced if we are greatly stimulated by a commitment to deliver attractive programming. Programming--content--will always reign as king. But given the choice to enjoy the programming in the old standard or in the new HDTV standard, the contest is over for me. Will we pay for HDTV receiving equipment if one or more signal providers gather up the courage to deliver us top flight HDTV program/signals to our homes via over-the-air, through a cable, down a fiber optic strand, etched on a little 5 inch disk (DvD), or on tiny digital video cassette tapes? How we start directing today our consumer electronics dollars will determine more than anything else that is happening now the future for advanced television and HDTV.</p>

<p>All aboard!</p>

<p>Dale Cripps, June 1995</p>

<p>Copyright 1995-2005</p>

<p>  </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>July  8, 2005  9:43 PM</b>
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
			<?=getComments(149)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 149)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/07/_1995_being_wright_for_hdtv.php" type="text/javascript" charset="utf-8"></script>
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