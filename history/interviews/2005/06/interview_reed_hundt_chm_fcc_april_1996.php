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
		AND e.entry_id = 120";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 120 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 120 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 120";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_reed_hundt_chm_fcc_april_1996.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (4) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 120";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Reed Hundt, Chm, FCC - April, 1996" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Reed Hundt, Chm, FCC - April, 1996" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Reed Hundt, Chm, FCC - April, 1996" />
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
	<title>HDTV Magazine - INTERVIEW - Reed Hundt, Chm, FCC - April, 1996</title>
	<meta name="keywords" content="high definition, digital television, make sure, transmission standard, say please, standard, industry, think, fcc, broadcasters, make, get, want, chairman, any, say, should, government, congress, been, market, need, hundt, new, digital" />
	<meta name="description" content="While Reed Hundt was chairman of the Federal Communications Comission (1993-1997) he was guided by two principles: first, the FCC should make decisions based on the public interest and second, the FCC should write fair rules of competition for the..." />
	<meta name="title" content="INTERVIEW - Reed Hundt, Chm, FCC - April, 1996" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Reed Hundt, Chm, FCC - April, 1996" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_reed_hundt_chm_fcc_april_1996.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="While Reed Hundt was chairman of the Federal Communications Comission (1993-1997) he was guided by two principles: first, the FCC should make decisions based on the public interest and second, the FCC should write fair rules of competition for the..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Interviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=120', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_reed_hundt_chm_fcc_april_1996.php">INTERVIEW - Reed Hundt, Chm, FCC - April, 1996</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 24, 2005</b>
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
				<p><em>While Reed Hundt was chairman of the Federal Communications Comission (1993-1997) he was guided by two principles: first, the FCC should make decisions based on the public interest and second, the FCC should write fair rules of competition for the communications sector.  </p>

<p>Under his leadership the agency conducted the first spectrum auctions in U.S. history. In the first two years of auction authority the agency raised nearly $20 billion for the national treasury. This fact was highly inspirational to those forecasting the value of the broadcast spectrum that was to be returned to the FCC after the digital transition was completed. That 20 billion amounted to more than 60 times the combined Congressional appropriations for the FCC in its entire 61 years. While not popular with the HDTV-only advocates, Chairman Hundt also stated his commitment to flexible rules for the use of spectrum. He reiterates that commitment in this interview. </p>

<p>Hundt advocated the promotion of competition within all five lanes of the information superhighway which the FCC regulates: broadcast, cable, satellite, wire, telephony, and wireless communications. He also wanted to promote greater choice for consumers, increased opportunity for businesses, and fair rules of competition. With passage of the Telecommunications Act of 1996 the FCC became responsible for implementing its "numerous and complicated provisions". </p>

<p>The Chairman was the first in his office to bring FCC operations into the communications age. He made himself accessible by participating in open, online conversations with the public and was the first FCC Chairman to have a personal computer on his desk connected to a network. He established the FCC web site as well.</p>

<p>Prior to his assuming his Chairmanship Hundt was a partner in the Washington office of Latham & Watkins, a national and international law firm. His work included legal and regulatory issues in emerging technologies, such as cellular telephones, direct broadcast satellite, and interactive television. He had Al gore's ear.</p>

<p>Hundt graduated from Yale College (1969) and Yale Law School (1974), where he was a member of the board of the Yale Law Journal. He was named Chairman of the FCC by President Clinton and was sworn in by Vice President Gore on November 29, 1993.</p>

<p>The ATSC standard was handed over to the Chairman in November of 1996. It appeared to languish unmoved for months in his office. Many in the industry feared a delay at the Chairman's desk would open the door to newer technologies that, in the heat of their promotion, might raise doubts as to the viability of the standard as it had been submitted. Both the computer industry and the Cinematographers Union had also belatedly asked the chairman to change the standard to reflect their interests (which were unexpressed throughout the nine years in which the standard was under development). In order to see where the standard was in the mind of the Chairman (and thus where it might be on the FCC the agenda) I asked for and was granted this interview. Some have credited the interview, which was published in time for massive distribution at the 1996 National Association of Broadcaster's convention in Las Vegas, with moving the standard off dead center. More realisticaly it was Congress who set the pace as Hundt explains in the interview.</em> </p>

<p>We open with his coming to the phone:</p>

<p>Hi, This is Reed Hundt. How are you?</p>

<p><strong>Good morning Mr. Chairman. I am very happy to have this opportunity to talk with you. We haven't spoken in awhile. In that interim an accumulation of things have stacked up that we would like to discuss now, if you have a moment?</strong></p>

<p>Go right ahead. </p>

<p><strong>I think the overarching question is: How do you see HDTV from within the Commission?</strong></p>

<p>I always consider this to be digital television because high definition is one, but only one, of the many different features of this new transmission standard. So, what we need to do is to complete our work in building an industry consensus behind the transmission standard. Saul Shapiro is taking the leadership here. He will be meeting with the industry representatives for coming to the final stages of the consensus building over the next few weeks. I am hopeful that in the month of May we will have been able to put the Grade A stamp of acceptability on the transmission standard.</p>

<p><strong>Would that be a result of the forthcoming NPRM (Notice of Proposed Rule Making)?</strong>Yes. </p>

<p>The Notice would ask: Is there anything wrong with this standard? We hope the Notice, in fact, meets with widespread acceptance. We are trying to get this acceptance in advance.</p>

<p>We all know that there are some who would like to adopt this standard for  purposes apart from broadcasting, and they may have some difference with it. How is that to be handled? <br />
It is a Notice. So if people have any disagreements they get a chance to write it in the record and tell us what they think. But I don't anticipate any serious controversy about the standard. "Standard" means a million different things when you get down to the engineering. This is a very technical set of issues and it is important that knowledgeable people will examine it. </p>

<p>But, I don't see any large policy questions, except one-don't you want broadcast to be able to continue to explore the flexible uses of this new transmission technology? I would think the answer is quite obviously, yes. That should be obvious, but that would be a huge change here at the Commission since we always raised major impediments to invention within the scope of the NTSC signals. Even now we have a backlog of proposals to use the NTSC signal for delivering data. I think that is ridiculous. Why should the Commission bar evolution and innovations within a standard? </p>

<p><strong>Is it then no longer important to the Commission whether the channel is used for a single HDTV broadcast or any variations that may have been proposed and talked about?</strong></p>

<p>That question has nothing to do with the standard.</p>

<p><strong>Some are puzzled that you have spoken on several occasions about the multiplexing options that few in broadcasting, if any, have intention of doing.</strong></p>

<p>Perhaps Fox said it a year or so ago. Or what Bob Wright said yesterday! In Communications Daily he said 5 or 6 channels. But look, if they are accurate, and if everyone is going to do two high definition formats with their 6 MHz, so be it. That is up to the marketplace! <u>I hope you express this to everyone</u>. I think that people should do what they want to do with this invention. I wouldn't think that Henry Ford would say, "I have invented the model T, but you can only use it to drive from your home to work. I don't want you to ever go anywhere else with it. Well, I am not interested in telling anybody what to do with this invention. I am in favor of small government, deregulation, and a market-oriented approach, and letting marketers and engineers together decide how to make the most out of this wonderful invention. In any event what we are talking about has nothing to do with the standard.</p>

<p><strong>It is amazing to me. Because people are taking enormous steps to try to convey to you a message that they are devoted to HDTV, thinking that has importance in your decision making processes. </strong></p>

<p>It is totally irrelevant to me. It doesn't have any impact on any decision that I will need to make. I agree we always talk about it, but I remain mystified about this holy grail of extremely high-definition resolution. I can't imagine saying to a photographer, "Look, I don't want you to use art, and I don't want you to think about content. I want this picture to be really sharp."  What would be the point of giving this kind of advice to a photographer you might hire to take a picture of your kids? What would be the point of us saying to anyone in the business community that the number one policy goal be that the picture be really sharp.  It is obvious to me that the number one goal would be that you be able to make the most out of the invention. If one guy thinks it is the way to get the biggest audience is to have a really high-definition picture, great! If the next person thinks his biggest audience is from having the option of five different programs,.. that is great with me to! There are many rooms in the digital television mansion. There is no reason for anyone to think that the government needs to interfere with the choices with the market.</p>

<p>I believe there is a perception that choices taken to emulate the NTSC service gives more credence to those demanding spectrum charges. <br />
I have often said that I think that spectrum fees-paying a monthly tythe to the government depending on what you decide to show-is a real bad idea. That is a way to meddle with the market, and I am against it. </p>

<p>If you are talking about auctions... The auction issue is a one-time issue. That is about who gets the licenses. Do they go to someone who will plunk down a chunk of change or do they go to the current broadcasters? </p>

<p>That is not our issue. We have no authority to decide that. Congress will decide that. When that is decided by Congress then we get to a different question. Should you charge broadcasters depending on whether they are showing multiple channels? Should you charge them a fee if they don't show high definition? Should charge them an extra fee if they showed some subscription television? My view is that every idea like that represents a meddlesome intrusion by government on the unknown possibilities of digital television. I am against it. Let the market work.</p>

<p>There is only one thing that the government, in my view, should ask of digital television. That is that it serve the public interest in some specific and quantifiable way. But serving the public interest does not, in my view, get coupled with requiring to pay a "bit tax" depending on the number of bits they devote to standard or high-definition. Those ideas are anathema to someone who trusts the free market, as I do. </p>

<p><strong>I think you have clarified that to the relief of those who think other ideas might be creeping into the process.</strong></p>

<p>Unfortunately, I don't have the say-so on this. Much of this is driven by budget debates which I don't have any say-so in. But that is where I am coming from, and it is not the first time I have said this.</p>

<p>I realize everyone focuses on the spectrum auction issue. Once that is decided you are into a different set of issues. Do you want "bit taxes"? I say, please, NO! Do you want the government to tell you what resolution and what formats to use? I say, please NO! Trust in the market. Do you want vague,  ambiguous public interest obligations? I say, please NO. Make them specific, quantifiable, tradable, and minimal. Do you want to have the government interfere in a variety of ways that digital television could be structured as an industry? I say, please NO. Let the market decide that. </p>

<p><strong>In the absence from any further legislation from the Hill regarding the allocation of the new channels, what will govern your process, and when will that be done following the May setting of the standard?</strong></p>

<p>All five of the Commissioners promised Congress that we would not get the licenses out in '96, or until the new Congress was formed and we get new guidance from the Congress coming in January of '97. We have to honor that promise. It was extracted from us by Tom Bliley (H, R-VA) and Bob Dole (S, R-KA) and a number of leaders in Congress. We ought to honor it. We will honor it. So, it will be the next Congress that tells us to pull the trigger on the granting of the licenses. What we ought to do before then is make sure the standard is OK'd and that we have raised all of the relevant issues about allocation-the process of describing what the license would exactly look like. So, we ought to get that work done prior to the  Congressional indication that I would expect in early '97 on who gets the licenses, and how they are distributed. </p>

<p>If you take a little historical view, that is pretty good. I have spent all of '94 and all of '95 nagging the Grand Alliance and my Advisory Committee to get the testing done, and they only got it done last November when the report came to us.</p>

<p><strong>There are complaints arising now that the standard has been on your desk since the 28th of November, and where is it going, and what has been done since?</strong></p>

<p>I wish we had gotten it in 1994! I nagged them for two years to get their work done. There was one excuse after another. But it does take time to get technology tested. Even now they have only begun to test it. I mean the testing of multiplexing is extremely limited. Almost no one knows the maximum number of channels of acceptable quality in the field. I think there is plenty more testing to do. I hope and believe that broadcasters will continue and do this testing even without my pressure.</p>

<p><strong>I understand that the testing lab (ATTC) is now in severe jeopardy. Funding has not been granted.</strong></p>

<p>That is a really sad commentary because it makes you think that the commitment of the industry for exploring the potential of this technology is ephemeral. You worry about that. If people are not going to really explore the capabilities of this transmission standard, then this whole process will end up producing a hollow win for the broadcasters. They won't be ready to do it. The cable industry has a lab that is working night and day to figure out how to do digital. Whole industries, with billions of dollar of R&D, are devoted to figuring out how to make cable a two way medium. The telephone industry relies on not only Bell Labs, but literally hundreds and hundreds of other projects to figure out how to get its infrastructure to deliver video. I think it would be a tragic mistake if broadcasters don't continue to explore the potential of this standard. Think of how truly sad it would be if the FCC said, "Well the standard is acceptable," and then broadcasters didn't even bother to explore its full potential. </p>

<p><strong>Do you think...</strong></p>

<p>I think the problem is that historically broadcasters have not acted together to develop their own technology. The Grand Alliance effort is an exception but even there it was not driven primarily by broadcasters. So, broadcasters don't have a history of working on their medium. One reason is that the FCC stultified innovation by freezing the NTSC signal for 50 years. This was a horrible policy mistake because we curtailed any initiative within the broadcasting industry. </p>

<p><strong>There are those that say it is not such a mistake because without it you would not have created this voluntary relationship between a telecast and a receiver maker and mobility of the product could be limited.</strong></p>

<p>That flies in the face of all logic. The cable industry has contractual relationship with manufacturers. The wireless industry has contractual relationships with manufacturers. The satellite industry is founded on a crucial deal between Thomson and Hughes (DSS). Every other industry knows how to have alliances in the marketplace that endure and deliver jointly-created services. Now, what in the world is the reason to think that broadcasters need the government to forge these combos? </p>

<p><strong>They may have a good answer to that. They usually say we need a firm standard set at the FCC to insure that the sender and receiver function at the most economical level.</strong></p>

<p>Look, broadcasters invented this standard themselves in conjunction with manufacturers over a period of almost nine years. There has been all kind of talk about the FCC's role. The truth is, our role has been between skimpy and tiny. That is good. This has been done by industry. It is the exact same process of innovation that has characterized the wireless telephone industry, the satellite industry, and the cable industry. If broadcasters are going to compete against their many rivals in delivering video, they need to learn from this experience and keep working together in voluntary industry groups to develop new uses for this transmission technology.</p>

<p>Let me put it positively. It is extremely predictable that the Commission will say that this standard is OK. After all, with respect to wireless we have said that CDMA is OK, and that TDMA is OK. With respect to the satellite industry, we are hands-off on their transmission standard. We are not even interfering in a major way with the cable industry, although they do have the bottlenecks that is a problem for broadcasters. </p>

<p>So, why would we not want to be equally lassie faire and market oriented vis a vis broadcasters. The problem here is that broadcasters need to make a commitment on a short and long term basis to fully explore this wonderful new invention if they want to make the most money they can from it. I have heard, by the way, that Westinghouse intends to do that. I don't know what you know about that?</p>

<p><strong>We know of their interest in high energy solid state emitters and some other things. There is always hope that a GE and a Westinghouse would provide special skills.</strong></p>

<p>My personal vision is that once this steps into the commercialization phase that there will be an unparalled explosion of things related.<br />
I hope so. It is all going to take R & D and marketing. </p>

<p><strong>What is then, the actual role of the FCC in this standard setting process? What is essential for the FCC to do in this case?</strong></p>

<p>We have to make sure that interference protocols are clear. We have to make sure that the standard is not proprietary. We have to make sure that anyone can use it. We have to make sure that it can evolve. You shouldn't need a lawyer to develop the potential of the technology. You should need engineers and scientist, not lawyers. This is just not going to be a controversial issue. </p>

<p>The allocations will be more problematic. Difference license holders will undoubtedly want more signal strength at the expense of the next guy. But we will deal with that. Everyone knows that at the end we will have to make some fair call.  Then, in '97 the new Congress will tell us what to do with the licenses, and we will do it. Hopefully, the government will not be asked to regulate, and we will be able to stay away from regulating the commercial activities. </p>

<p><strong>Thank you Mr. Chairman.</strong></p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 24, 2005  6:20 PM</b>
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
			<?=getComments(120)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 120)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_reed_hundt_chm_fcc_april_1996.php" type="text/javascript" charset="utf-8"></script>
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