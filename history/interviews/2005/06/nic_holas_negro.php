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
		AND e.entry_id = 104";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 104 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 104 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 104";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_nicholas_negraponte_media_lab_1994.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 104";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download INTERVIEW - Nicholas Negraponte. Media Lab (1994)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="INTERVIEW - Nicholas Negraponte. Media Lab (1994)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="INTERVIEW - Nicholas Negraponte. Media Lab (1994)" />
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
	<title>HDTV Magazine - INTERVIEW - Nicholas Negraponte. Media Lab (1994)</title>
	<meta name="keywords" content="being digital, aspect ratio, open architecture, point view, media lab, going, digital, think, people, want, system, those, see, television, right, say, because, time, years, consumer, could, technology, might, things, why" />
	<meta name="description" content="Nicholas Negraponte is the author of Being Digital. He is a founder and the director of the Massachusetts Institute of Technology's uniquely innovative Media Laboratory. The Media Lab is an interdisciplinary, multi million dollar research center of unparalled intellectual and..." />
	<meta name="title" content="INTERVIEW - Nicholas Negraponte. Media Lab (1994)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="INTERVIEW - Nicholas Negraponte. Media Lab (1994)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_nicholas_negraponte_media_lab_1994.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Nicholas Negraponte is the author of Being Digital. He is a founder and the director of the Massachusetts Institute of Technology's uniquely innovative Media Laboratory. The Media Lab is an interdisciplinary, multi million dollar research center of unparalled intellectual and..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=104', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/interviews/2005/06/interview_nicholas_negraponte_media_lab_1994.php">INTERVIEW - Nicholas Negraponte. Media Lab (1994)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 20, 2005</b>
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
				<p><em>Nicholas Negraponte is the author of <u>Being Digital</u>. He is a founder and the director of the Massachusetts Institute of Technology's uniquely innovative Media Laboratory. The Media Lab is an interdisciplinary, multi million dollar research center of unparalled intellectual and technological resources. It is focused exclusively on study and experimentation with future forms of human communication, from entertainment to education. Programs include: Television of Tomorrow, School of the Future, Information and Entertainment Systems, and Holography. Media Lab research is supported by Federal contracts as well as by more than seventy-five corporations worldwide. Negroponte is also co-founder and back-page columnist for Wired magazine.</p>

<p>Negraponte studied at MIT, where as a graduate student he specialized in the then-new field of computer aided design. He joined the Institute's faculty in 1966, and for several years thereafter divided his teaching time between MIT and visiting professorships at Yale, Michigan, and the University of California at Berkeley.</p>

<p>In 1968 he also founded MIT's pioneering Architecture Machine Group, a combination lab and think tank responsible for many radically new approaches to the human-computer interface. In 1980, he served a term as founding chairman of the International Federation of Information Processing Societies' Computers in Everyday Life program. Two years later, Negroponte accepted the French government's invitation to become the first executive director of the Paris-based World Center for Personal Computation and Human Development, an experimental project originally designed to explore computer technology's potential for enhancing primary education in underdeveloped countries.</em></p>

<p>INTERVIEWED in 1994 by Dale Cripps<br />
<strong><em>HDTV Newsletter</em></strong></p>

<p>In re-reading this interview today it became clear to me how far ahead of many of the television and motion picture strategist Nicholas Negraponte had grown. His ideas, once thought radical, can be seen today throughout the entire communications landscape. At the time of this interview the HDTV standards setting work was under a great deal of pressure to once again change itself into something else, this time all-digital. That move to all-digital by all of the proponents of HDTV, sparked by General Instrument's demonstration, had not yet occured. Mr. Negroponte's interview was not entirely welcome by most of those working on HDTV standards here and abroad as he devalued in a sentance or two everything analog. He speaks of both the European HD-MAC system (an analog satellite transmission system once destined for Europe) and MUSE (an analog satellite transmission system once destined for Japan) as being dead. He was right. I think some of his ideas are just now coming into focus for many in telephone and cable. HIs comments with respect to how motion pictures will be distributed reads like today's headlines. I hope you enjoy this rare interview with a true visionary. _Dale Cripps<br />
  <br />
<strong>Dale Cripps:</strong> Nicholas, you had been drawing the circles of print, video and computer closer together. In the early days there was a great deal of skepticism among the large manufacturers (about your vision). I see that skepticism subsiding rapidly. Where are we on the closing of these circles? (Negroponte was fond of drawing three circles that overlapped on their way to converging.)</p>

<p><strong>Negraponte:</strong> All you need to do is look at acquisitions and mergers over the past couple of years. It doesn't take much effort to see that people are beginning to realize that you want to be in more than one medium. Whether this is the right reason to be converging or not you have people like Newscorp and others who are getting into all three arenas. It is not often known but Newscorp owns eTech, the computer mapping data base company. From that perspective they are certainly coming together. This is one of several but a reasonable bellwether because those decisions are business decisions not just technology predictions. </p>

<p><strong>In that they are business decisions they must have some technology support. What areas of that do you see completed and what are need to be completed in short and long term?</strong></p>

<p>What is bringing them together is very simple. Everything is going digital. That is such a banal comment today but I remember as recently as two years ago I would say things like "the future of television is digital and people like Bill Shreiber and Jae Lim would say the opposite. In fact Bill is still reticent about going fully digital. Jae Lim, as recently as a year and one half ago in the New York times was quoted as saying he didn't think it would work. Since then everyone has changed. I am much happier to see people change then be stubborn. </p>

<p>The Japanese, according to my intelligence are really at the edge of abandoning Hi-Vision. The Europeans HD-MAC dead in the water too. They are all going digital.</p>

<p><strong>Let's explore that further. I recently returned from Japan and there are complaints from those not invested in MUSE. Yet, Mori Morizono of Sony is skeptical that the digital system will work to meet the business requirements of the U.S. broadcasters. </strong></p>

<p>I am under a lot of non-disclosure agreements and I think I would be violating them if I say why he is saying that. The truth is that a lot of companies in Japan, especially Sony, have more or less concluded that it is better to switch to digital sooner rather than later. What they can or cannot say is very complicated. I am not trying to jeopardize our friends at Sony who are really caught between a rock and a hard place.</p>

<p><strong>You say that HD MAC is dead?</strong></p>

<p>Yes. It will hobble through Barcelona (the Olympics) and then pack up and go away. There is just no question about that.</p>

<p><strong>Do you think that what we are doing here in this country in testing systems and validating them will give to those regions of the world a system that they can adopt?</strong></p>

<p>No. What we are doing in this country is very, very important because it will change the face of history and basically bring digital techniques to everyone's attention -- prove they are possible. But I don't believe any of the systems in front of the FCC would be adopted by us or anyone else. </p>

<p>My bet, if you ask me to say what will happen, is that MPEG II, if done right, will turn out to be the de facto television standard of the world. The reason for that is not just because I think if they did it right I would be enthusiastic. I am saying it for another reason. That is very often when we criticize Hi-Vision MUSE and HD MAC, both of which are extraordinary vulnerable to technical criticism, we forget that the biggest criticism is the one we never mention, or, at least not as openly, is that it is Japanese. HD-MAC is European. If one of the "contestants" were to be selected, which I don't believe will happen-, it would per force be American. Television has historically been a very nationalistic phenomenon. The French have still not recovered from the fact that SECAM was not accepted and that 825 line television was not accepted. </p>

<p>MPEG II has this wonderful attribute of being A) International; and B) the work of some 75 or so companies from all over the world from many different disciplines--computer, television, etc., etc. It could be adopted with pride. People could adopt it without sort of agonizing whether it was Japanese or European. I am more interested in seeing the three regions of the world--Japan, Europe, and the U.S.--I think it is very important that they do come together. MPEG II is the only hope I see at the moment. </p>

<p><strong>One of the proponents uses, of course, MPEG++. This is an international company made up of Europeans and Americans. Is that enough?</strong></p>

<p>Well, those people are right here at the Media Lab as we speak today and we are spending the entire day with them. I was delighted when I saw them use the name MPEG ++. I have not looked at the details. I think the consortium is a very interesting one. Again, there is going to be many political forces to not select them for nationalistic reasons. AT&T and General Instruments, with or without MIT, are by definition in a stronger position. But MPEG++ may be a step in the right direction. I want to underline that it is a <em>maybe </em>because I don't know the details.</p>

<p>What I do know is that all of the submissions to the FCC are extraordinarily handicapped because they all started off with this unfortunate emphasis on high-definition. </p>

<p><strong>Where should the emphasis have been placed?</strong></p>

<p>Being digital.</p>

<p><strong>At any line rate?</strong></p>

<p>At variable line rates. If I made a list for you of all the advantages of being digital it would be a list that would have a dozen advantages written on it and high-definition would be very nearly at the bottom of that list. </p>

<p><strong>Is high-definition, in fact, an important evolution of television or is it more overkill?</strong></p>

<p>It is absolutely unimportant. Really not important by comparison to being digital. I can't tell you how strongly I believe that. </p>

<p>We are talking about the ability to embed digital information which isn't displayed. That may be indexing what is coming -- control characters for peripherals -- doing all sorts of things to the signal. The signal may be carrying the algorithm to decode it. There is just a world of things you can do with digital video and annotations you can make to the signal are extraordinary. </p>

<p><strong>Let us paint a vision of the world in ten years. </strong></p>

<p>Let's break it into four constituencies--equipment, broadcasters, program makers, and the consumers. One has to look at all of them, which has not been the case up to now. Primarily we have looked at the equipment manufacturers. Quite frankly, the broadcasters are very unenthusiastic and I think rightfully so. </p>

<p>I think from the equipment manufacturer's point-of-view the opportunity to build with what Bill Shreiber coined as "open architecture." Even while everyone bellyaches about it I think that open architecture, from the manufacturer's point of view, will be a very, very interesting market. Then you can start innovating with some peripheral, accelerators, and things you can do - upgrading incrementally and letting people buy in over time and "grow" their system.</p>

<p>The broadcaster's as soon as they are in a digital world are in what I call the "bit radiation" business. All of a sudden what those bits represent is so flexible and so variable... Let me give you a specific example:</p>

<p>The FCC is reviewing 20 Mb/s solutions right now. Let's say they select one and give you as a broadcaster a 20 Mb/s license. What are you going to do with that license? You are not going to broadcast HDTV. You are going to broadcast 3 or 4 channels of NTSC. Then if you are clever you are going to broadcast 3 channels of NTSC, one radio program, two pagers, digital newspaper and some other unknown data broadcast service. Then all of a sudden on a Saturday afternoon in your local area there is an important college football game you might devote 8 Mb/s to the football game, discontinue one of your NTSC channels (may be not run the newspaper). Then in the middle of the night you might be broadcasting 6 or 7 newspapers. In other words all of a sudden you with your 20 Mb/s space be your own micro FCC allocating your spectrum as you see fit. From the broadcaster's point of view that is really very, very interesting business opportunities. </p>

<p><strong>Channels from time-to-time are moved and arouse viewer distress. Their program is not where it once was. Are we not apt to run into this same sort of thing with flexible use of spectrum?</strong></p>

<p>In order to be that flexible the receiver has to be designed accordingly. It won't happen overnight. The receiver just has to attend to that. The signal carries with it again in some of those non-visible bits the kind of information that automatically attends to that. </p>

<p>From the programmers point-of-view I think it will be very exciting in terms of the fact that you will be able to download programs to receivers. The people making programming are still divided. This is where the circles are most divided today. TV people and print people overtly keep them separated.</p>

<p><strong>Why are they doing that?</strong></p>

<p>The belief that they are sufficiently different media that reporting in one versus the other doesn't have a cross over effect. I think that is not true and it will take awhile to prove itself. </p>

<p><strong>Is this the same stance that print saw in radio broadcasting?</strong></p>

<p>Exactly. I was driving a rented car the other day and turned on the radio to whatever channel I was tuned to. I was picking up the sound track to CNN television on the radio. It is a small example, but it is already happening. </p>

<p>When you think of television as downloading then something like the 6 O'clock news, especially if you are using cable or fiber systems, you start compressing the signal so you can deliver one hour video in about 5 seconds loaded into your receiver and you start randomly accessing it in very, very simple ways. You do some of the classic "tell me more" techniques.</p>

<p><strong>Will this be down loaded to disc, tape, and solid state..? </strong></p>

<p>It could be any. Again, in an open architecture we might find that big magnetics or magneto optics as a way one might go for the next five years and before the turn of the century it will certainly be solid state memory.</p>

<p><strong>Are going to download movies?</strong></p>

<p>Absolutely. Whether you download them or "trickle charge" them using very low bandwidth or whether you see them in real time - all three options are going to exist. When the phone company provides the one way 1.5 Mb/s line into your home, then you will be looking at it in real time. If you are using a lower bandwidth you have to "trickle charge" it. If you are using high bandwidth you will "burst" it in there. </p>

<p><strong>Do you forecast the day when the consumer will be charged by the bit?</strong></p>

<p>That is a good point. Yes, it will be pay-per-view per bit. If you are looking at a 10,000 line flat panel display ten feet high in your living room with friends looking at a football game in the afternoon and I am looking it on my kitchen counter on an 8 inch diagonal I suspect I will pay $.30 and you will pay $3.00. </p>

<p><strong>If I were a programmer and distributor today should I be looking for ways to be sending the highest quality?</strong></p>

<p>That depends on the program and it is, again, a business decision. How much the channel is being used for other things, etc. But if your channel capacity wasn't a variable of any significance then yes, you would want to broadcast the highest quality and then let people take any piece of it they want. </p>

<p><strong>That provokes an image of a stratum of signal providers. Some might be local with lower origination while others, perhaps the existing networks of today, passing through the highest bit rate rates or quality. </strong></p>

<p>Exactly. As long as it is the same salable architecture that is used to represent the signal it all just makes so much sense.</p>

<p><strong>You mention the telephone company. Do you think they will end up with the rights for creating content?</strong></p>

<p>I hope so. I believe they will for sure be in the delivery business. I am a great advocate of that because I think it should be switching phenomena. I really believe that each receiver should be able to receive separate signals throughout the entire nation so all 150 million sets could have a 150 million different TV programs running at any one point in time. It might be the some program in some cases, like the news just being run offset at funny times. It might be people accessing different movie data bases. It could be downloading and people looking at different sub-sets of the evening news. It is one you do by switching and not by loops and not by trying to run a fiber system through Queens, New York that has a 150 channels on it. I don't want a 150 channels. Nobody wants 150 channels. We want one channel. It just happens to be that we want the one channel we want at the time. The way you get it is not to select one out of 150 channels but have a system that allows you to specify your channel.  </p>

<p><strong>In effect we are program picking anytime we see fit?</strong><br />
Absolutely. </p>

<p>I was told by various Hollywood studios that a few months ago surveys were being run by computer companies with the view of digitizing all the studios' film vaults. These vaults would have access from all delivery services as well as consumers from this master center and be sold on some transactional basis.</p>

<p>Even more important in those systems is that you can use the same system to look at trailers to help you decide what you want. One of the killers when you go to the video rental store is that you can never find what you want. I often walk out with something I don't want to watch or have already seen. If I can call up some trailers and see them postage stamp size on the screen--wondering through a data base--I can make an informed choice. </p>

<p>We have moved in to point four--the consumer benefit. That is an enormous benefit. The selection process is normally not discussed. It is the delivery process that gets discussed. </p>

<p>A lot of this business of advanced television has been driven by the idea of what the consumer might want. But with a few experiments of late where in Europe the Space wide-screen (625 line) system is marketed and in Japan with the MUSE HDTV there has not been a tremendous interest. </p>

<p>I think wide aspect ratio is a bazaar subject. I listen to people like Jae Lim (MIT) who say the only thing that everyone agrees on is wide aspect ratio of 16:9. I have to wonder if that is not just another red herring. It should be a variable aspect ratio. It is unclear to me if I want to see curtains on the right and left for 50% of my programming or I want to see letterboxes on 50% of my programming.</p>

<p><strong>Aspect ratio was thought to be the differentiating thing even with the set turned off. I didn't see it too differentiated in Europe or Japan with the set on.</strong></p>

<p>Right. It differentiates best with the set off. I like letterboxes because it makes for a nice crisp horizontal line at top and bottom if you have a good receiver.</p>

<p><strong>What are the other consumer benefits? Programming has to lead technology. What are we apt to create for the consumer in "being digital" in an understandable "sound bite" that he is able to quickly comprehend and say, "yes, this is what I have to have"?</strong></p>

<p>There are some simple ones. Any consumer that is told, "listen, you can have movies on demand." There are 50,000 movies (not counting those from India and Hong Kong" and "you can have anyone you want for your $3 or $4 and it will help you select which one. That is one way and is passive and doesn't do much. The sound bites get a little bit longer and harder. Those consumers aren't really into the transaction side of computing in the home. Most of the big payoffs will start in more of the transaction oriented information providing. For example. The Yellow pages are used and they include not only the opportunity to consummate a transaction but they also get personalized. Low and behold on your screen you see the driving structure from your house to this business place because the program provider like Newscorp owns the data base that can automatically show you how you can get there.</p>

<p><strong>That suggests a print out device as well. </strong></p>

<p>Absolutely. Hard copy will be a very big piece of it. A lot of consumers if you told them this was the medium that if when using the open architecture concept with a few dollars will provide you the way to edit all these 8mm video cassettes you have in a shoeboxes... I mean there are all sorts of things that start emerging. </p>

<p><strong>How would the editing work?</strong></p>

<p>I can't for the life of me figure out why no one has come out with a simple two cassette editing system for the consumer. I can't believe that everyone is so asleep at the wheel on that one. But, it is going to happen sooner or later and consumers really want it. We have several people here at the lab working on prototypes of that sort. But I have got to believe industry is also doing it. </p>

<p><strong>I still haven't heard that one strong sound byte that sells the consumer the moment it is heard.</strong></p>

<p>It doesn't work that way in the consumer world. Take the fax machine. There is not a person who could live without one once they have it. Yet most people five years ago had not even heard of it. Consumer marketplace doesn't quite behave that way. It took audio CDs 4 or 5 years to get off the ground. Now they don't have vinyl anymore.</p>

<p><strong>But that was a crystalline benefit. Everyone said "oh I get it, audio is improving and it is more convenient. Do you see any opportunity for some all embracing term. Is there is phrase or ketch word?</strong></p>

<p>I use the term "personalized television", which sounds like an oxymoron right now with everyone thinking that television is the supreme mass medium and definitely not personalizable. But that is a sound bite that isn't going to make the market pick up and pay attention as quickly as some manufacturers would like. But I think that the personalization of television is the general umbrella. </p>

<p><strong>Do you think that business plans such as SkyPix are viable?</strong></p>

<p>Do I believe that direct broadcast satellite for applications like that one are going to make it; my answer is yes. But their life-span will be ten to fifteen years. It is interim technology.</p>

<p><strong>Is digital the last technology we are going to have to grapple with? Have we reached a threshold where the fundamental technology upon which we have for building is final?</strong></p>

<p>Once you are in the digital it says more about representation than about future invention. One of the very long term - ten to twenty years - opportunities is the machines ability to understand the information and filter it and sift through so as not to provide bandwith expansion of information but bandwidth compression for you, the overloaded.</p>

<p>It starts to look at TV for you and starts reducing some of this massive amounts of information as humans do today if you have a secretary or friends who look at things and tell you about them. </p>

<p>So, it becomes a large filter with your aims and interest in tact and all others discarded. </p>

<p><strong>You have talked about the newspaper of the future. One can scan efficiently the Wall Street Journal in twenty minutes a day. I can't scroll that fast. </strong></p>

<p>Right. What you are doing with your eyeballs personalizing the WSJ by reading headlines. We are looking at most 5% or less of the information. You are right that today's newspaper "technology" lends itself to personalization by virtue of your human perceptual system. That is one way of reading the newspaper. It is what happens to me frequently. People cut things out and paste them together and send me a little piece of email. This is another form of personalization. Quite frankly those stories are very interesting. I get a weekly publication from Japan that way and when I travel I get all sorts of clips that way and they put them together. Those "personalized newsletter" are like the WSJ that I do read from cover to cover because some human has done some very good filtering. So, those are the two alternatives. It is not that we can make the scrolling function as good as the eyeball function - I don't think it will ever be.</p>

<p><strong>You are suggesting some form of template?</strong></p>

<p>I am not suggesting that in the sense of a list of interests, etc. I am talking about the sort of thing that a good secretary does. My calendar is one of the most revealing pieces of changing information about me -- who I will see tomorrow and who I saw yesterday. Just looking at something as specific as someone's diary or schedule is very dynamic form of personalization as well as the fact, yes, I would be interested that my first cousin, who I have not seen in a long time, has just published a book on something or other that is reviewed in some remote journal.</p>

<p><strong>There has been in some circles a clear resistance to Negroponte. Some will stand up in meetings, some in private discussion where your name creates come agitation. Those involved with the early days of HDTV felt they suffered from the fact that your ideas were, perhaps, strong enough to be carried forward as ideas, but not strong enough to be realized while at the same time dissembling their activities.</strong></p>

<p>That may be true. But since the whole world has gone digital all I can say to those people is, "I told you so." Now that everyone agrees it should be digital my next hobby horse is that it be scalable. There is a great deal of resistance to that.</p>

<p><strong>Why?</strong></p>

<p>I am not sure why. I was never sure why there was so much resistance to digital except that people had vested interest. I think scalability is the same thing. All of these people who have submissions to the FCC and other places are really expecting people to trash their HDTV systems and start all over again when we have 2000 and 3000 line displays. It is mind boggling. </p>

<p><strong>Do you think 2000 line displays are every going to be in demand? One could logically believe that 1000 lines are enough forever.</strong></p>

<p>Let's recall where the 1000 lines came from. It came from CRT technology and nothing else. There were no studies what-so-ever on what resolution people would want on screen size, or anything. It was basically a random number based on what people felt they could do economically with CRTs.</p>

<p><strong>That isn't the way I read that story. I see that the work done by NHK and the seminal work by Bill Glenn determined that you couldn't improve visual sharpness at three times the height of the picture.</strong> </p>

<p>You could argue that if I am going to stay ten feet away from the white board I have on my wall, 2000 lines is fine. But if I am going to go right up to it and interact with specific parts... I need to think of television as a lines per inch medium, not a lines per picture height media. </p>

<p><strong>As we do with computer screens?</strong></p>

<p>Exactly. If you are really a hard core broadcaster and believe that people are going to sit on couches looking at tubes, then you could argue that some number - and I don't think it is 1000 - may be the maximum for the so called 30 degree experience. I think that is real old fashioned thinking. It is not the right way to go.</p>

<p><strong>When people here you say things like this they say, "what he is trying to do is delay us and we don't know why he is trying to delay us. What we need to do is to take this $5 billion investment we have and produce some revenue with it so we can go on. Why doesn't that man just shut up and let us go on with the technology we have available now to the public." </strong></p>

<p>Two answers. Needless to say I have no vested interest in having a company loose money or try to delay something that would be in the public interest, or anything like that. The first answer by analogy... do you remember SelectaVision, the RCA capacitive disc? I remember Mr. Griffiths, the CEO of RCA at that time. That disc was coming out at then. Public occasion after public occasion I said it was just absolutely the wrong thing to be doing and that it was a dead technology and one had to look to optics and not these capacitive discs. RCA were strong with me. They said, "Why are you sabotaging this? We spent $50 million developing this system and we have got to bring it to market to recuperate our costs." I said to one of them if you bring it to market you are going to loose ten times your investment. Low and behold when they pulled it off the market they had lost $500 million. The same thing is going to happen with Hi-Vision and HD-MAC, if they do it. So when people are asking why I am delaying them they should be very thankful because if they do bring that stuff to market they are going to loose billions. It is just not going to fly.</p>

<p>That is one answer. The other is: let's pretend I am wrong and it is going to fly. It is going to fly at the expense of the consumer. If Philips or Thomson, for example, says we have invested so much in this analog system and we want to bring it to the market to recoup our costs, by definition that has to delay digital introduction because they need a window in order to recuperate their costs. These broadcasters all have to tool up. The consumers have to buy things. It is not only going to delay, but it is going to be in the worst interest of the consumer. Nobody is on the consumers ‘side. I think the window of opportunity for analog television has closed. </p>

<p><strong>Some might say that there are people right now who might say, "I will not live long enough to see a digital solution and would appreciate HDTV now even if soon faced with a obsolescence. </strong></p>

<p>The reason is because you will have digital on the market in less than three years. We are not talking about an enormous delay. At this point in history it will take just as much time to introduce HD-MAC in its analog form as it will to introduce a proper digital system. </p>

<p><strong>That is interesting and provocative because the Europeans have said that even if a perfect solution were to be had it takes ten years to reach pan European agreements.</strong> </p>

<p>That is because they have been looking at it not as an industrial standard but as a political arena. I think MPEG I is a disaster, but note it only took about 12 months. MPEG II is going to be decided upon before the end of this year and could be going into manufacturing. You could see chips and sets come out within less than three years. You ask about SkyPix. Hughes has just signed an enormous deal with Thomson to provide digital receivers for their direct broadcast satellites. Cable Labs has an RFP out for a digital system and will be in the home in less than 24 months. Indeed, when it gets there it will be converted to analog to fit the current receiver. It is moving fast. </p>

<p><strong>So, your recommendation is very clear. But there is also a perceived window of marketing opportunity from the publicity generated the last 6 years that cannot be wasted. Can they allow that to pass?</strong></p>

<p>It is harder for governments to cut their losses than companies. It turns out that in this case fate will play its hand perfectly. One can just sit back now because there is no way in Hell that Hi-Vision or HD-MAC is going succeed. So we can focus now in doing it properly and the Europeans know that. </p>

<p><strong>I believe we are on the threshold of global broadcasting. Can we get a global system?</strong></p>

<p>Absolutely. That is one of the whole reasons to go digital. It can be scan line independent, frame rate independent. and aspect ratio independent. All of those things will make the programmers absolutely delighted. That is exactly where we are going and I don't think we are going to miss the target. We are going to finally make it. </p>

<p>In the digital world standards take on a very different nature. You can think about them more on what I call meta-standards. We can agree on basically how we will transmit encoding algorithms so the receiver is somewhat standards independent. It says, oh, that is one of the Italian programs and it decodes it. If you don't have the software to do that you might have to go to your Radio Shack and but a little IC card or diskette that loads that in. If I am a program manufacturer I might even broadcast in some weird standard and force you to go and buy that decoder as the way I make the income from my program. I can use this in very, very creative ways. </p>

<p>I think we have to start letting the machine do the decoding and transcoding of these systems much more automatically. </p>

<p><strong>Is this a programmable CPU? </strong></p>

<p>Always, say the TV set makers, you are adding a burden of cost to make this flexibility universal. The consumer may use but a fraction of these features and therefore, the argument goes, why should everyone bear the burden?</p>

<p>It is just inaccurate. If you are talking about somebody buying a vanilla open architecture receiver that does nothing more or less what they are doing today. The chance that you are adding cost is very real. However, I now give you a "pause" feature with a little card you can slip in. As soon as the phone rings I push the pause button it starts recording and when I return it continues from where I left off. It is a shift device to allow me not to miss something. I also have now an electronic still camera or a VCR editing system - another option. I can now print out hard copies. When you start adding, and not too many functions, the cost of adding that function will be so much less than if you had to do it with a whole other box. Right now in my home I have sitting side by side a laser printer, a copier, and a fax machine. Those are very expensive. If I told you I can sell you a device that does all three functions that device probably will cost more than any one of those three sitting there, but certainly less than the cost of the three. So, the cost arguments are gratuitous. They are not accurate.</p>

<p>Thank you Mr. Negroponte.</p>

<p></p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 20, 2005  9:09 PM</b>
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
			<?=getComments(104)?>
			<div class="dottedline"></div>

			<? if (4 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 104)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_nicholas_negraponte_media_lab_1994.php" type="text/javascript" charset="utf-8"></script>
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