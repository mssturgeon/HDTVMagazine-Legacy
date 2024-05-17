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
		AND e.entry_id = 95";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 95 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 95 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 95";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1996_the_third_coming_of_television.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 95";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1996 - The Third Coming of Television" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1996 - The Third Coming of Television" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1996 - The Third Coming of Television" />
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
	<title>HDTV Magazine - 1996 - The Third Coming of Television</title>
	<meta name="keywords" content="electronic cinema, motion picture, transmission system, hdtv movement, grand alliance, hdtv, new, system, television, digital, electronic, spectrum, standard, cinema, those, transmission, picture, business, still, much, consumer, world, motion, years, first" />
	<meta name="description" content="This article was first published in 1995 in Communication Systems Design Magazine--a trade magazine serving the telecommunications infrastructure. High-definition television (HDTV)-the fresh and brilliant new generation of telecommunications-is coming. At least you heard and read that for a decade or..." />
	<meta name="title" content="1996 - The Third Coming of Television" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1996 - The Third Coming of Television" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1996_the_third_coming_of_television.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="This article was first published in 1995 in Communication Systems Design Magazine--a trade magazine serving the telecommunications infrastructure. High-definition television (HDTV)-the fresh and brilliant new generation of telecommunications-is coming. At least you heard and read that for a decade or..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=95', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1996_the_third_coming_of_television.php">1996 - The Third Coming of Television</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 18, 2005</b>
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
				<p><em>This article was first published in 1995 in Communication Systems Design Magazine--a trade magazine serving the telecommunications infrastructure. </em></p>

<p><br />
High-definition television (HDTV)-the fresh and brilliant new generation of telecommunications-is coming. At least you heard and read that for a decade or more. It sounded magnificent-five times more picture information than standard TV. State-of-the art five channel audio. And, oh yes, and cinema shaped screens of 16:9. Industry watchers waxed eloquently about it saying these spectacular and infinitely attractive images would make billions upon billions of dollars for the primary stakeholders. TV networks heralded it as its own inevitable successor. Where ever one turned there articles waxed eloquently about the wonderful crisp, clear life-like images... and there were others who reported the darker side--international subterfuge; callous self-serving strategies, and international intrigues fueled from a fear that industrial, economic and political supremacy would go to the big winner in the HDTV race. The U.S., it was shouted in Congressional hearings, would have to lead the HDTV movement if this nation was to maintain its economic prowess and technical standing, not to mention maintaining a superior national defense. As quickly as HDTV rose to prominence it faded. Negative intonations crept into the few stories run outside of those in the trade press. It was declared by many to be: </p>

<p>-Too expensive; <br />
-Not good enough; <br />
-Not scalable.enough <br />
-Incompatible. <br />
-Not matched to the available displays <br />
-Foreign dominated, so shouldn't be adopted; <br />
-Too much sacrifice by the broadcasters; <br />
-A user of too much spectrum; <br />
-Not interesting to the public (nor have they still a clue as to what it is); <br />
-Too divided because of no agreement on a production standard </p>

<p>Both The New York Times and Washington Post reported that HDTV was not so much a consumer issue as it was a ruse for spectrum snitching by the cagey American broadcasters. If not that spin there were the endless chidings over the delays in the standard setting process--a process that admittedly draged on too long. Moreover, many new consumer electronic developments had pushed themselves into the foreground leaving HDTV as little more than a obtuse sidebar. Digital highways, Direct Broadcast Satellite services, five hundred channel television universes, telephone companies re-inventing themselves as wide band carriers, interactive TV, the computer becoming the television, and a host of spectacular video game platforms all overshadowed the hapless would-be 'image king'. Those recalling the glowing promise finally asked, <em>"What happened to it? Where is HDTV?" </em></p>

<p>When I was asked by Communications Systems Design to write an article on HDTV, a concern set in. I worried that any story on a single point would be inadequate; yet telling the whole story would be too exhausting. Since HDTV has fallen off the radar screen one might ask; why bother discussing it at all? <em>Isn't it dead?</em> Many have been led to think so. But this kind of reaction is typical for a new product entering an established market--especially one that is sure to upset the foundations of the status quo. I approached the editors saying that "HDTV is a very, very big and complex subject". It is not merely a new television set, but rather a long interlinking chain of new things beginning with the way images are captured, switched, manipulated with effects, compressed, recorded, transmitted, decoded and displayed, and even how they are viewed. Not only this, but with the higher image quality the use of this technology at each link goes well beyond what standard television could satisfy. New markets are sure to spring in every direction when the consumer markets drive cost down. Perhaps, I said to the editors, my years of observation and reporting might best be used to provide you a brief overview to get the flavor and scope of the potential of HDTV. They agreed. </p>

<p><strong>Background </strong><br />
The HDTV movement began in 1963 from within Japan's huge public broadcaster, NHK (Japan Broadcasting Company). NHK must, by Government decree, spend a small percentage (less than 1/2 of 1%) of their multi-billion dollar income for research and development in television for the public benefit. They do so in cooperation with the electronic manufacturers of Japan. Ventures from this cooperation become commercial products from the manufacturers. </p>

<p>HDTV bounced on to the world stage in 1981 with demonstrations given by NHK in the US and Europe. The goal of the early movement was to achieve an international agreement on a single worldwide production standard. The first proposed HDTV production standard-with parameters of 1125 scanning lines and 60 fields-came out of the Japanese research. The parameters were set so as to make conversions to all existing analog transmission standards around the world as easy as possible. Europe and other 50Hz regions were slightly disadvantaged with these parameters due to the 60Hz field rates in the Japanese proposals. The Japanese proposal was endorsed by the US. The hope of a single standard was sunk in 1987, the result of protective politics in Europe. Europe offered their own standard as the single standard and the world fell into two camps-one with 1125/60 parameters and the other 1250/60. That sapped much of the strength from the movement. </p>

<p><strong>Early Transmission </strong></p>

<p>First transmission demonstration for HDTV appeared in 1985. This solution from Japan (MUSE) required 8.3MHz from a satellite and suffered a softening of the image when in motion. Europe stepped up their research and development to gain parity with Japan and delivered their own HDTV satellite broadcasting system two years later (HD-MAC). Both systems, while digital in much of their internal operations, were in the end analog and fell on hard times when the whole world decided it must go digital. The Japanese were determined to be first every step of the way and introduced MUSE HDTV as a commercial product in 1991. Eight hours a day experimental broadcasting fed a very weak market. The MUSE system has taken considerable heat and been declare obsolete before it was ever launched. Still, this analog system is slated to run until the year 2007 when a new satellite will be launched with increased capacity. At that time a conversion to all-digital will be made. This left the HDTV movement gasping for air as still another apparent misstep showed up on the ledger for this unlucky technology. </p>

<p>The US was on the verge of industrial paranoia in the late '80s and early '90s. Worry that Japan and Europe were sure to gain a technical advantage with HDTV swept through Washington. President George Bush responded by ordering his FCC Chairman, Al Sikes, to get America in the lead. Broadcasters had by 1987 asked the FCC to look at the HDTV question, freeze spectrum allocations in the broadcast bands, cause a HDTV transmission system to be created and tested, and then allocate spectrum to broadcast it if more was needed. Prior to assuming duties as Chairman of the FCC, Sikes had been head of the NTIA, an advisory organization to the White House on telecommunications policy. He was widely regarded, knowledgeable, and had extraordinary influence. He was our leader and the HDTV movement followed every word. But the word was transferred out when the Clinton Administration was on the verge of coming in. Sikes dove for cover within the executive offices of The Hearst Corporation in NYC and a relative unknown, Reed Hundt, assumed the chairman's office. Hundt had little reputation as a technology expert and the FCC lead was left to the influence coming from outside its office. HDTV was no longer the thing to save American face, that being replaced by the internet. </p>

<p>Broadcasters, notably NBC, had first insisted that HDTV be compatible. David Sarnoff, the man who was singularly responsible for much of what we call broadcasting today made that same decision for compatibility when it came time to standardize color. "He would do the same today," claimed Dr. Kerns Powers, retired vice president of the David Sarnoff Research Center in New Jersey. Several proponents came forward with compatible systems-some limited to the existing spectrum, others using an additional 3 to 6 MHz. Sikes had been convinced that to adopt a compatible system the old NTSC scheme would have to be retained as the basis. That was not going to get America in the lead. Taking a very bold step he insisted on a simulcast approach, where the old channel continues as it has for a period of time and a new digital channel is phased in. Proponents rose to that bait and delivered what no one as late as 1990 thought possible-an all-digital HDTV transmission system confined to within a 6MHz channel. </p>

<p>So far this project is limited to fiber optic transmission and within the boarders of North America. Many believe that the proof of concept for Electronic Cinema must come from foreign soils. </p>

<p><strong>Coming Home </strong></p>

<p>For HDTV to come to the home a complete revolution in television technology will have to be completed. Nothing remains the same. The technology will inevitably induce a revolution in the business of television itself with a probable change in powers. No one can be left unaffected-not the program producers, the signal providers, the transmission providers, the advertisers, the manufacturers, the retailers, and certainly not the end users. Each will need to adapt to the new to avoid obsolescence. It is a system designers dream. The great danger is that the movement gets off to a half hearted commercial start and is dragged back by the inertia of the old. Then it would pause in mid air and careers and investments would come crashing down. It is a difficult task, full of risk unless there is an orchestrated effort where the manufacturers, the program suppliers, the signals providers, the alternate media groups, and the end users all play the same tune together. Such cooperation is not unheard of, but rare indeed. </p>

<p>Rigorous tests were devised and a testing lab was built in Virginia (the Advanced Television Test Center) to measure the performance of the proponent systems and determine any interference characteristics. The compatible approach-really an augmentation system delivering an Extended Definition-was allowed to test along with four other proponents (just in case the others failed). The digital proponents were Zenith, MIT, General Instrument, the ATRC (made up of NBC, Thomson Consumer Products, David Sarnoff Research Center, Philips, and Compression Labs). Following testing and evaluations in 1992 a meeting was held at Tyson's Corner, VA to select the winner. But no winner could be chosen. An Advisory Committee to the FCC process (Advisory Committee on Advanced Television Systems) called for a unifying of the system proponents. ACATS Chairman, Dick Wiley (former Chairman of the FCC) wanted a best of the best system to be built and submitted as the one sole US HDTV terrestrial transmission system. This call from Wiley finally galvanized the four intense competitors and "the Grand Alliance" was born. Work on the new<br />
 system was recently concluded and the hardware assembled in New Jersey at the Sarnoff Center moved to the Test Center in Virginia for final testing. The results will be evaluated and then delivered the end of this year to FCC Chairman, Reed Hundt, where a rubber stamp approval is anticipated. Spectrum will then be assigned broadcasters and they will have some time to apply for building permits for the new digital transmission system. That will give the US its MPEG-2 based HDTV system for terrestrial broadcasters by the first quarter of next year. Cable and DBS will interoperate with this standard. The manufacturers will have HDTV products on the market within 16 months after the standard is set if they see that anyone is serious about transmitting it. The system is closely related, though not identical, in design to the European DVB (Digital Video Broadcasting) counterpart. One significant difference is in audio. The Grand Alliance system chose the Dolby AC-3 system while the Europeans chose Musicam, a European development from Philips. Both are 5.1 systems (five channels-left, right, center, rear right, rear left, and sub-woofer). The Grand Alliance system will operate in either progressive scanning mode (787.5 lines progressive) or interlace (1080 scanning lines interlace). Computer groups have insisted that to start with the interlace option is a grievous mistake. Everything is progressive in the computer world for reducing text and motion artifacts. The view has been that HDTV displays will be important to the web as well as a movie in your living room. The compression scheme is thought to be very important for decoding internet transmitted material. <br />
 <br />
The Grand Alliance system can do more than encode and decode HDTV. Within a 6MHz spectrum a station could choose lesser resolution and allocate bits in the 6MHz spectrum for 4 or more additional programs in a lower quality (equal to studio grade NTSC). Additionally, there is a 'data-only' capacity to the GA system. This is designed in for still-to-be-found ancillary data services, such as stock market information delivered to a TV set or PC. This flexibility has raised concern that HDTV will be abandoned in favor of more program services but no solid business vision has emerged on how to do that. More importantly, Congress has smelled money in the water and auctioning spectrum that is used for non-traditional public services has become a popular idea. This threat of auction of spectrum has made the broadcasters to say, oh, yes, we always thought HDTV was the primary purpose for the new channel. "Get the spectrum free and worry later what to do with it," has become the underlying strategy for today. </p>

<p>Alternative media-cable, telephone, DBS, and the VCR have not seen any reason for jumping into HDTV. Neither cable nor satellite in the final analysis have the spectrum to use for HDTV. While cable will go digital to increase program capacity, HDTV is still thought too "spectrum intensive." To the satellite operators this has become crucially important. In fact, the DSS DirecTV/USSB success is tied directly to digital compression (MPEG-! & 2) and thus many more programs can be tucked in per transponder. This breakthrough in compression allowed for DBS to compete with cable and was the sole reason that financing supported it. None of the cable program providers, such as HBO, see any advantage to pioneering HDTV signals as well. Nor do any within the pre-recorded industry. HDTV will have to be driven not by economic considerations but rather the love of doing something better. That will require the faith that a sufficient market will arise fast enough to satisfy capital requirements. </p>

<p>Even while overshadowed by all of this HDTV has not had the decency to go away. It has, indeed, proved to be one of the most tenacious of all consumer electronic contenders surviving years of negative news and bad luck. Why? It is not due to Media Labs Director, Nicholas Negroponte. Promoting his vision that the computer and television are becoming one device he has traveled the world over to discourage HDTV. In his new best selling book, Being Digital, he asks rhetorically if the public is complaining about picture quality, the shape of the screen, or the quality of the motion? He says there is no evidence that a demand has arisen for these things, and so why bother? Besides, the digital image no longer depends on the number of lines scanned and should be scaled to whatever you want, to any screen shape you want, and to whenever you want. In the day of super computer power the manufacturing for achieving that is not in question (though cost still is). How you sell an ill-defined product to the consumer is a big question. </p>

<p>The reason HDTV has persisted is that those who have seen it simply like it. They believe fervently that others will too. Consumer retailers need well-defined products if they are to survive in a fickle marketplace. The hugely successful DBS roll out by RCA and DirecTV was simple to understand. You can get all the programs from a tiny little dish, and they are better because they are digital (buzz word hit). You don't need pounds of manuals to make it work. HDTV is itself simple. It is a receiver that has a clear big picture when viewed at three times the picture height. It also will play old standard TV, but you might want to sit back a few more picture heights. In this way it is like an AM/FM radio more than a computer. The PC, little over 10 years old, has not proven its durability as an appliance of choice over a 50 year stretch. But television has, and thrived for all those 50 years. Judging from how many are trying to get into the television business it is safe to say it will be with us for another 50 years. That is long enough to warrant moving up to HDTV if you like quality. </p>

<p>HDTV may actually be on its most forceful leg of the march home. Within a year the standard for transmission will be set. That will stabilize things and allow products to come to market. If the manufacturers are serious about selling these wonderful machines they need to arrange massive demonstrations to "hook" the consumers. Alternative uses in government, medicine, and industry are sure to carry it for awhile. The manufacturers have done most of the hard work and have tooled for a major introduction. As far as the consumer is concerned the success of HDTV is far more dependent upon easily accessible signals than hardware. </p>

<p>Where those signals will come from is still the 64 million dollar question. Some think that all signal providers must move forward at the same time for it to succeed. Others think that from the efforts of one champion pioneer all others will fall like dominos to some form of HDTV. HD pioneer David Niles sees it as a brand new business built in the branches of the existing. Like FM radio, it could draw up those who want the climb. The rest can wait to take the technical and program droppings from the tree. Not since Alexander Graham Bell's time has the audio/visual side of the communications industry been more vulnerable to a wild card than today. The person with the most appealing and well-timed message to the global public stands to gain a huge following. Technology and deregulation permits that to be done more quickly than ever before. While the main body of the communications industry holds conservatively to their cash generating technologies a new wild card is free to act on forward looking technologies. </p>

<p>The motion picture business is the most notable of these conservative groups. Each motion picture is a new and unproved industry. To threaten such a high risk venture with added unnecessary risk in technical innovation is, well, unbankable. Disney rang up $400 million in VHS cassettes sales of The Lion King. Would they tamper with that kind of success by supporting some new item that was years from major penetration? On the other hand, reselling old libraries in a new format is a four smile consideration. They understand this better today than in the past. It was not long ago when Jack Valenti (MPAA president) was sent out preaching that the VCR was the worst possible thing and would make a travesty of copyright values. Recent studio support by Paramount, MCA, and Warners for the new Digital Video Disc (DvD)-a compact disc holding 9 Gigabits per side-shows clearly that times are changing. But DvD doesn't require consumers to buy a new receiver-just a new player that is compatible with the audio disc. Hollywood has clearly a self interest since there is lower pressing costs for discs over tapes. What I am saying is that Hollywood will act to lower its cost or raise its revenue as long as they see those benefits in black and white. </p>

<p><strong>Electronic Cinema-a Major Use For HDTV </strong></p>

<p>The motion picture business is not developing electronic cinema. Yet electronic cinema is a wave not to be missed. Those who rule electronic theaters will guide much of the money for Hollywood. The electronic cinema story has been around for a long time, but only when HDTV appeared in the mid '80s did it look like it had a technical answer. Yet, Hollywood studios have not given serious effort to define and encourage it. To some electronic cinema looks like the ideal business to franchise-something for system designers to contemplate. The first requirement for electronic cinema is making a durable video projector (believed by this author as not far off). The projector must be priced right, be bright enough (10,000 lumens), and reliable enough (light source changing only) to meet or exceed the existing film projectors price and performance. Around that one device-the projector--can be built the video cinema with thousands of theaters geared to local conditions and audiences while, like a McDonalds, tied to a higher center. The big payoff for Hollywood comes in many ways. According to a privately circulated and well respected report a five billion dollar per year gain from foreign distribution is immediately realizable if electronic cinema is installed. Scratched and patched film passes itself off as movies to millions of people in Eastern Europe, Africa, South America, and parts of Asia. Electronic distribution cannot only increase the quality of a presentation but make timely the international cinema going experience. That timeliness translates into dollars. As the wealth of the world increases that means big bucks, real big. Marshal Mcluhan said that television created the global village but today's motion picture business operates as if serving distant colonies. Publicity for a film is now global. The international television and radio networks, like CNN International and Murdoch's vast media empire, insure that. Globalism in television is at its dawning and still Bay Watch is seen by over a billion loyal viewers every week! Billions of viewers can be made aware and excited in one day about a new movie opening. Under today's distribution schemes it is only those movie-goers in the US and Canada who have immediate access to the 1400 to 2000 theaters (seldom more prints are ordered) which have the first run release prints. With electronic cinema and encryption (to protect against pirating) a motion picture would be electronically released to tens of thousands of viewing sites around the world. Both shipping and duplication (prints) is saved. A much faster return on the film's investment in just one of the many benefits. Another is the ability to program these theaters as one might with a television network. With live or taped material you can do those thing needed for drawing patrons into theaters irrespective of the drawing power of the feature film. This was the practice years ago with shorts and the news and can be updated to very exciting levels. The combination of movie and television programming knowledge will pay handsome dividends in this new business. _1995 Dale Cripps<br />
 </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 18, 2005 11:00 AM</b>
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
			<?=getComments(95)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 95)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1996_the_third_coming_of_television.php" type="text/javascript" charset="utf-8"></script>
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