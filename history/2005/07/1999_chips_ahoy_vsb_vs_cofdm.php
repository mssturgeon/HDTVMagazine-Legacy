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
		AND e.entry_id = 158";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 158 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 158 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 158";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/07/1999_chips_ahoy_vsb_vs_cofdm.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 158";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1999 -- Chips Ahoy (VSB vs. COFDM)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1999 -- Chips Ahoy (VSB vs. COFDM)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1999 -- Chips Ahoy (VSB vs. COFDM)" />
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
	<title>HDTV Magazine - 1999 -- Chips Ahoy (VSB vs. COFDM)</title>
	<meta name="keywords" content="hdtv magazine, frank eory, jeff davis, nat ostroff, motorola frank, vsb, motorola, cofdm, chip, sinclair, •, those, multipath, hdtv, magazine, data, nat, signal, been, system, chips, ostroff, nxtwave, say, low" />
	<meta name="description" content="This is one of many articles written about the once considerable 8-VSB vs. COFDM controversy. That controversy is laid to rest in this nation with 8-VSB clearly being the choice to nearly everyone's satisfaction. I say nearly everyone, because in..." />
	<meta name="title" content="1999 -- Chips Ahoy (VSB vs. COFDM)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1999 -- Chips Ahoy (VSB vs. COFDM)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/07/1999_chips_ahoy_vsb_vs_cofdm.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This is one of many articles written about the once considerable 8-VSB vs. COFDM controversy. That controversy is laid to rest in this nation with 8-VSB clearly being the choice to nearly everyone's satisfaction. I say nearly everyone, because in..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=158', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/07/1999_chips_ahoy_vsb_vs_cofdm.php">1999 -- Chips Ahoy (VSB vs. COFDM)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>July 24, 2005</b>
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
				<p><em>This is one of many articles written about the once considerable 8-VSB vs. COFDM controversy. That controversy is laid to rest in this nation with 8-VSB clearly being the choice to nearly everyone's satisfaction. I say nearly everyone, because in this field perfect agreement without qualification has never proven to be the case. I will be adding more of the articles penned until the whole story is laid out for you.</em> _Dale Cripps <br />
_____________________________________________</p>

<p>Reports raced throughout the Internet on August 19th (1999) that a "new chip" (dubbed by cynics as the "Mystery Chip") had been developed in Pennsylvania to adequately handle dynamic multipath reception for 8-VSB receivers. "I must say the timing is 'perfect.'" offered Sinclair Broadcast Group's Nat Ostroff. A news release surfaced from Nxtwave Communications in PA on the 24th.</p>

<p>Nxtwave Communications Inc., of New Town is a spin-off of the David Sarnoff Research Center, themselves no stranger to consumer product innovations. "Nxtwave began 3 years ago as a Sarnoff incubated company," said Sarnoff's CEO, Jim Carnes. </p>

<p>Not only Nxtwave, but Motorola also released on August 23rd a press release heralding their new chips capable of handling dynamic multipath for 8-VSB. This chip also came from a partnership between Motorola and Sarnoff.</p>

<p>"The real mystery to me is where these people were when Sinclair was begging for 8VSB receivers that worked." asked Microsoft's Tom McMahon. </p>

<p>Less skeptical was Lynn Claudy, vice-president of Technology of the National Association of Broadcasters. "They have been working quite unnoticed for several years, Only recently have they come to anyone's attention." </p>

<p>Nat Ostroff (Sinclair) had also heard something of them and invited Nxtwave to take part in the Sinclair "tests". He received no response. "It seems a little bit early to make claims for your chip when it has not yet been tested, let alone in the field," he was quoted saying then. "Others came to us with 'solutions' that did not prove out in field test." He adds "But having said that, I truly hope they have what they say they have."</p>

<p>Lab test of the new chip use signals from simulated environments. Many conditions were taken from recorded field locations in Baltimore. "That is a lot different than from real world," acknowledges Claudy. He is not alone in looking forward to a tested solution to the paralyzing challenge laid down by Sinclair. "It would be a disaster to dismiss 8-VSB at this late date," says Sarnoff's Jim Carnes, adding, "and it won't be necessary." Carnes believes that all of the worry "will be DTV history to look back upon in 4 months time." Claudy agrees.</p>

<p>Bob Graves, Chairman of the ATSC, certainly hopes these devices work. He is faced with marketing the US 8-VSB transmission system abroad and acknowledges that Sinclair's work has done a great deal of damage to his international mission. </p>

<p>Broadcom is another equalizer company who will surface soon with their solution on a chip. The chips from each vendor had been kept under tight security wraps until now. Some think the timing of these announcement is just too cozy and is setting up the industry with new complicated considerations so manufacturers can get past the Christmas season before the other shoe drops. But these chips have been under development for over a year. They are following current product introductory patterns. Few outside of essential parities were aware of the work going on. Just last Wednesday a high-level meeting was held in Washington, DC by those in support of 8-VSB. They met to determine what would be the best response to the Sinclair initiative. ATSC, MSTV, NAB, CEMA and others debated without conclusion on how or with what they could respond. The chips had not been disclosed to even those in attendance..</p>

<p>Nat Ostroff, the architect of the side-by-side test of 8-VSB and COFDM at Sinclair in Baltimore, has said repeatedly that the American system should have the "same" receiving characteristics as any system available anywhere in the world, i.e.,like COFDM. Ostroff has also declared repeatedly his neutrality as to which system finally prevails, just as long as it works. COFDM is clearly a competitor, but like all competing things they offer competing features or design promises. Ostroff has been favoring the inclusion of COFDM in the FCC standard as an option to use rather than excluding 8-VSB in favor of COFDM exclusively. With ample co-signers from other group broadcasters, Sinclair is within days of being ready to issue a petition to the FCC asking for COFDM inclusion. </p>

<p>With the announcements from Motorola and Nxtwave Sinclair's petition plans are left pending a further detailed examination of the claims of these chip makers. The petition has not been dropped as there are still thorny issues with the receiver manufacturers that may have to be addressed formally. The FCC has advised consultants today that these equalization chip announcements alter their view from within the Commission, which had been growing in receptivity to accepting a petition from Sinclair and issuing a Notice. If proven in field test, these chips will change the way everyone is viewing the question, including and potential signers of the petition. Getting them used will be the other companion story.</p>

<p>So, what can broadcasters expect? We contacted Motorola on the 24th. I talked to Jeff Davis, Vice President of Global Sales, Imaging and Entertainment Solutions group, Jim Farrell, Manager of Marketing Communications, Imaging and Entertainment Solutions group, and Frank Eory, Designer, Digital TV Operations, Imaging and Entertainment Solutions group about the performance of the chip.</p>

<p></p>

<p><strong>INTERVIEW</strong></p>

<p><em>Motorola has developed an equalizer chip for "sub $20 in quantity." Drawing liberally from a vast experience in equalization technology from their industrial background Motorola claims they are delivering the most advanced equalizer ever be be deployed in a consumer electronics package. </em></p>

<p><strong>HDTV Magazine:</strong> What have you done to solve the problem illuminated by Sinclair?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> We attacked the problem of large dynamic and static echoes head-on with what I believe to be the world's most advanced equalizer ever to be deployed in a consumer receiver. This is to elemenate multipath from the equation so that from the broadcasters point-of-view the only issue is signal power in determining coverage. </p>

<p>We spent several weeks in lab testing. Field test are in progress as we speak. We are throwing all kinds of ugly VSB signals at this thing, including the Sinclair-Lombard Street and Sinclair Harbor Apartments scenarios--at least the lab emulation's of those. We went even beyond those scenarios once we saw how much margin we had with those multipath ensembles. </p>

<p>Basically, at least from laboratory measurements, we have demonstrated that multipath is not a problem, both very large static echoes as high as .1 db below the desired signal. With the Sinclair scenario quoted 10 Hz flat fading and 10 Hz dynamics on those strongest echoes, and in the ATSC Grand Alliance echoes, we have gone as high as 20 Hz on dynamic multipath. Quite honestly we are still trying to characterize the whole performance space of echo amplitude and dynamic phase rate to explore the extreme limits of what this thing can do. </p>

<p>HDTVMagazine: Then you confidently claim that you are doing all that COFDM is doing?</p>

<p>The one thing that COFDM is claiming, which I think is an area of challenge for 8-VSB receivers in general, is mobile reception. Different people define that different ways. We have clear evidence we can support pedestrian mobility--a guy walking around with a lap top with an antenna poking out the back--sort of low speed mobility. But the stuff they are doing in Europe in trying to demonstrate reception at 200 MPH down the Autobahn. That is more challenging for the ATSC system because we don't have the luxury of falling back to some very low data rate. In Europe they go to QPSK and put in extreme amounts of FEC (forward error) encoding and, yes, they can demonstrate some high speed reception, but not very much data at those speeds. That is all a "flexibility of standards" issue, but not something the ATSC was designed for.</p>

<p>The 20Hz dynamic is a lot more than leafs blowing in the wind or people walking around the room. Like I say, it easily satisfies pedestrian mobility, but falls something short of high speed freeway traffic. </p>

<p><strong>HDTV Magazine:</strong> Nat Ostroff has continued to say that he is neutral as to which system is finally used as long as it delivers "the same" as the one he can now have, i.e., COFDM. Does your chip satisfy this condition, or a percentile of that condition? How can we rightly compare it? Is there a set of testing procedures that will allow an apples to apples comparison?</p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> When you say the same, we have a lot of variables there that relates not only to the type of terrain you need to deal with, but the factor in power and coverage from the transmitter. </p>

<p><strong>HDTV Magazine:</strong> Yes, I am just wondering out loud how you factor in whatever trade offs are left?</p>

<p>What was really demonstrated in Baltimore was the inadequacy of the early generation of the receivers. Clearly there were some issues from the UHF propagation point of view with indoor reception. What is the static and multipath environment really like with a set top and bow tie antenna? Some of those things were not addressed by the Grand Alliance test and not really addressed by first generation receivers. Nat drove home that point that these receivers have got to get better. We have been working for more than a year now to answer that question. </p>

<p><strong>HDTV Magazine:</strong> Did you move from Nat's earliest initiative, now over a year ago? </p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> We have been working with our partner Sarnoff on a number of devices for over two years now. This is actually the second chip to have come out of that partnership. We are very pleased that Nat gave us some additional difficult scenarios thanks to the data capture that was done and posted on the web by Oak Technology. That gave us real world signals that we could throw at this thing, and then say, "yeah, it can handle those with no problem. What's next?" We are out now working with various broadcasters and some of the consultants that know where the nasty spots in various cities are. We will go out and demonstrate with a bow tie antenna in a Manhattan apartment soon, etc.</p>

<p><strong>HDTV Magazine:</strong> Does your approach require training signals?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> We are not worried about them at all. One of the key points that a lot of receiver designers recognized a long time ago is that the training signal sequence in the ATSC signal itself doesn't happen often enough--some 25 milliseconds. That is not enough to cope with the dynamics, so you have to go to blind techniques and the training signal is just some other data that is nothing special. </p>

<p><strong>HDTV Magazine:</strong> What is left to do?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> Quite frankly for the home receiver or low speed portable, we have taken the multipath equation completely out of the picture. So, the only challenge left is to improve the speed of those dynamics, which translates to vehicle speeds. That is really the only problem left to solve. </p>

<p><strong>HDTV Magazine:</strong> Is that foreseeable as we advance in silicon?</p>

<p><strong>MOTOROLA--FRANK EORY:</strong> As far s COFDM doing that today...it is not clear just how much value that is. As I mentioned, it is extremely low data rate (from COFDM). It seems to be enough for a single SDTV program in an 8 MHz TV channel, but what does it mean if it was in a 6 MHz US channel? </p>

<p>Something else that Nat and other COFDM proponents have not really addressed is: Can you do HD in a 6 MHz channel? I am very familiar with the COFDM standards--more than you might realize--and there are some entries in the table in terms of spec code rates that do support HD kind of data rates, but if you actually do one of those, how robust is it? No one has demonstrated that yet.</p>

<p><strong>HDTV Magazine:</strong> You will have no data rate penalty using your VSB chip? </p>

<p><strong>MOTOROLA--FRANK EORY:</strong> No, it is in the standard at 19.39 Mb/s</p>

<p><strong>HDTV Magazine:</strong> Take this opportunity to address all of the broadcasters in the US and Canada who might have concerns from the Baltimore initiative. What do you want them to know? </p>

<p>I would summarize it this way: This chip COMPLETELY removes the multipath issue from the equation.</p>

<p><strong>HDTV Magazine:</strong> "Completely" is a big word.</p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> Yes, COMPLETE with the caveat that it (the usage) is home (for) reception and pedestrian portable. I am not going out on the limb saying it will handle mobile reception. But it completely eliminates the dynamic and multipath issues. For the broadcaster the only significant variable becomes that of transmitter power to determine coverage. </p>

<p><strong>HDTV Magazine:</strong> Does this, then, support Bob Graves' contention that 8-VSB is the superior of the two?</p>

<p><strong>MOTOROLA, JEFF DAVIS:</strong> This is still to be demonstrated. Nat has his data and we will have our data, but in theory it is better on the fringe areas because it should require less signal-to-noise than COFDM. The more extended of the Sinclair test--those further out sites--claim that they saw no real advantage of 8-VSB over COFDM. But again, those were early generation receivers where they didn't deal with multipath adequately. So who can say (they were optimized for distance)? But we have an equalizer now that makes everything look like a gausian noise channel essentially. It cancels the multipath so that the rest is straight forward text book--how much signal power do you have? How much signal-to-noise ratio do you have at x miles from the transmitter. That should be the only variable that the broadcaster should be concerned with from this point on. </p>

<p>Thank you very much. </p>

<p>Hearing of these claims Ostroff said, "We are very excited about the prospects of these new chips." </p>

<p>Now comes another issue. The fact that there are chips to solve these problems does not guarantee they will be used, or if used, will not discriminate against terrestrial broadcasting with a higher box price than for a cable or DBS box. "We cannot tolerate that," says Ostroff. Lynn Claudy thinks it's good to be mindful of these remaining implementation issues and encourages someone to be the authority that mandates receiver performance. He first looks to the ATSC, and the NAB's<a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>has already suggested that the FCC get involved. Nat Ostroff does not want a two-tiered price structure with DBS or cable boxes being cheaper. With both the Nxtwave and Motorola chips entering the market at under $20 ($22 in quantities of ten thousand for Nxtwave) Moore's law would suggest that price will be very trivial in a few years time. In the mean time the cost of electronics is not the big issue in HDTV, where display and cabinet are so dominant in end pricing. But for boxes that convert DTV to an NTCS receiver, or a DTV SDTV receiver, an added cost at retail of $60 to $100 could be market-impacting. More on this topic at a later time.</p>

<p>More important now is to keep heads cool, evaluate the claims in a setting (why not back to Baltimore? Nat extends the invitation.) that will not provoke additional controversy, and heal the rift that has grown between broadcasting and their essential partners--the manufacturers. </p>

<p><br />
Dale E. Cripps</p>

<p><br />
 <br />
 <br />
 <br />
From Motorola <br />
 </p>

<p><strong>Feature Set --</strong></p>

<p>• High performance robust complex equalizer<br />
• Glueless interface with 10-bit industry standard<br />
• A/D converters, accepts pass-band<br />
• samples at 25 MHz as VSB input<br />
• Processor communication through an I 2 C serial interface<br />
• Digital on-chip timing recovery - no external crystal required<br />
• Provides gain control signals<br />
• Reed-Solomon decoder, Trellis/Viterbi decoder, deinterleaver<br />
• Signal interface for glueless connection to the<br />
• MCT4000 or a PCMCIA card for conditional access systems<br />
• Transport Stream interface with error checking and bit setting<br />
• All digital architecture for cost effective silicon implementation<br />
• 1.8 V operation voltage<br />
• 160 QFP packaging</p>

<p><br />
The MCT2100 is a single-chip all digital demodulator implementing Vestigial Sideband (VSB) demodulation and Forward Error Correction (FEC) functions for the reception of digital terrestrial broadcasts. It complies with the FCC 96- 493 Report and Order for terrestrial DTV broadcast that specifies the VSB modulation system. The MCT2100 achieves extremely high performance using a minimum number of standard, low cost external components to provide a complete 8 VSB demodulation system at an extremely competitive cost. A VSB demodulator must efficiently compensate for all the factors affecting the digital terrestrial broadcast. It must handle artifacts such as multipath signals and gain variation. At the same time, fast aquisition and low error rate are mandatory. </p>

<p>The MCT2100 uses a unique implementation of novel algorithms to meet all of these requirements and much more. It features low power dissipation in a low cost, industry standard, leaded surface mount package. Because of its high integration, simplicity of external design and straightforward interface, the MCT2100 enables fast design cycles and time to market. Motorola's VSB demodulation solution was designed with system effectiveness in mind. The MCT2100 consists of an active front end for timing recovery, AGC and pilot tracking together with an integrated back end for deinterleaving, error correction, serial or parallel data output and status reporting. Pins for gain control and IF data allow the MCT2100 to easily interface with other system devices. A host interface through an I 2 C bus is included along with an event interrupt signal to provide simple glueless control from the processor. Using years of experience in digital signal processing, Motorola has developed a solution, which is very reliable, design efficient and cost effective, thus providing customers the best solution for market success. </p>

<p>Copyright 1999 - 2005</p>

<p> <br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>July 24, 2005  2:59 PM</b>
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
			<?=getComments(158)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 158)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/07/1999_chips_ahoy_vsb_vs_cofdm.php" type="text/javascript" charset="utf-8"></script>
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