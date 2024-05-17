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
		AND e.entry_id = 131";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 131 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 131 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 131";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1996_the_digital_hdtv_grand_alliancerobert_graves_commerce_committee_testamony.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 131";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony" />
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
	<title>HDTV Magazine - 1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony</title>
	<meta name="keywords" content="digital television, front auctions, grand alliance, free air, advisory committee, television, digital, spectrum, auctions, hdtv, channels, fcc, broadcasters, atv, analog, congress, plan, technology, front, free, services, system, standard, committee, commission" />
	<meta name="description" content="By Robert Graves Before the Commerce Committee, United States House of Representatives Good morning. My name is Robert Graves and I represent the digital HDTV Grand Alliance-AT&amp;T, General Instrument, MIT, Philips, Sarnoff, Thomson, and Zenith-the partners who developed the digital..." />
	<meta name="title" content="1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1996_the_digital_hdtv_grand_alliancerobert_graves_commerce_committee_testamony.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="By Robert Graves Before the Commerce Committee, United States House of Representatives Good morning. My name is Robert Graves and I represent the digital HDTV Grand Alliance-AT&amp;T, General Instrument, MIT, Philips, Sarnoff, Thomson, and Zenith-the partners who developed the digital..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=131', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1996_the_digital_hdtv_grand_alliancerobert_graves_commerce_committee_testamony.php">1996 - The Digital HDTV Grand Alliance--Robert Graves Commerce Committee Testamony</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 26, 2005</b>
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
				<p>By Robert Graves<br />
Before the Commerce Committee, United States House of Representatives</p>

<p>Good morning. My name is Robert Graves and I represent the digital HDTV Grand Alliance-AT&T, General Instrument, MIT, Philips, Sarnoff, Thomson, and Zenith-the partners who developed the digital high-definition television system underlying the advanced television (ATV) standard recommended to the FCC by its Advisory Committee. I also serve as Chairman of the Advanced Television Systems Committee, a group of more than fifty entities developing standards for digital television.</p>

<p>I'd like to discuss a great technological achievement--digital high-definition television-offering theater-like, widescreen images and 6-channel CD-quality sound, as well as a fundamental improvement in the National Information Infrastructure. Over the last decade, a unique combination of consistent, bipartisan leadership from the Congress and the FCC--and a half-billion dollar private sector investment--has yielded the best digital television technology by far in the world. Our plea today is that government not forsake this superb plan right when it's about to pay off for the American people.</p>

<p>Before addressing spectrum auctions, let me summarize several key points. First, broadcasters must offer HDTV to remain competitive. Broadcasters understand this and plan to make S)TV the primary use of the ATV channel. Second, consumers want the dramatically improved performance HDTV offers. Third, besides dazzling pictures and stunning sound, HDTV will give consumers a high-resolution display and a huge data "pipe" that can deliver a host of other information services, and rapid penetration of entertainment HDTV will lower costs for HDTV applications in education, medicine, business, and national defense. And finally, capitalizing on this American technological triumph will create and preserve tens of thousands of highly skilled jobs and engender economic growth.</p>

<p>An early pivotal FCC decision has driven this successful effort--the simulcast approach whereby existing broadcasters would borrow a second channel to begin transmitting ATV signals while continuing to provide analog TV on their existing frequencies. This meant pulling a rabbit out of a hat--inventing technology to use the taboo channels already allocated to television, but unusable because of interference--providing a practical means for upgrading to digital television without disenfranchising the owners of 200 million analog TVs. It's a loan, not a giveaway.</p>

<p>Generally, using auctions to assign spectrum to competing applicants is a good idea. But auctioning these "loaner" channels is a bad idea that would render broadcast ATV stillborn, undermine free over-the-air television, lock in spectrum inefficiencies, and grossly reduce auction proceeds.</p>

<p>In the first place, any sensible discussion of up-front auctions must assume that the channels would be used for television or other data broadcast services using the proposed ATV standard, because these slivers of spectrum can only be used efficiently for one-way broadcast applications, and even inefficient other uses would require years of additional development and testing. Such auctions would yield far less than the inflated estimates often repeated.</p>

<p>But even so, up-front auctions don't make sense. Would small, local broadcasters even bid? Do we want large outside corporations to supplant today's local broadcasters-just because they have more money?</p>

<p>And what about the future of free TV? If these channels are auctioned, digital television, especially HDTV, will develop as a geographically spotty, premium pay service rather than the ubiquitous advertiser-supported service Americans enjoy today. Free, local, over-the-air TV is vital to our cultural and political fabric, and we should not give it up.</p>

<p>Even just for budget balancing, up-front auctions don't make sense. After analog transmissions cease, the digital channels can be repacked much more tightly, yielding huge blocks of unencumbered, nationwide spectrum that will be far more valuable than the small, noncontiguous taboo channels, bringing perhaps ten times more than up-front auctions.</p>

<p>And auctioning the taboo channels now would mean never getting the analog channels back, and locking in grossly inefficient spectrum use for decades.</p>

<p>Finally, up-front auctions would delay HDTV generally, and render it stillborn for free over-the-air TV. This would throw away America's technological lead, threaten jobs and global competitiveness, and thwart the delivery of valuable services.</p>

<p>Rather than up-front auctions, Congress and the FCC should hasten the conversion to AT; recap the AT channels once analog transmissions cease; organize the recovered spectrum into large, nationwide blocks; and assign the recovered spectrum using auctions. Such a course will enable free over-the-air television to compete, will vastly improve spectrum efficiency, and will maximize the proceeds from auctions.</p>

<p>Unlike up-front auctions, proposals for an "early give-back" of the analog channels have some merit. They offer many of the same advantages as our recommended approach, however, the stated time periods are too ambitious, especially for broadcasters in smaller markets.</p>

<p>We're in the home stretch of an international digital video horse race with a three-length lead, with proven digital technology that has leap-forged efforts in Japan and Europe. But at this late stage, second-guessing of the FCC's well-conceived plan gravely threatens our success-second-guessing by some members of Congress, and quite frankly, by Chairman Hundt--alone among the five commissioners and alone among the four FCC Chairmen who have led this unparalleled bipartisan effort over nearly a decade.</p>

<p>As more members of Congress become fully informed, we're confident that Congress and the Commission will follow through to win this race. We urge Congress to reiterate its support for the FCC plan upon which we've relied, and to direct the Commission expeditiously to adopt an AT standard and issue AT licenses to broadcasters in order to capture the benefits of this fertile technology for the American people. It would be tragically misguided and patently unfair, and would set a damaging precedent if the government were to jerk the rug out from under this splendid American success story</p>

<p>Thank you.</p>

<p><strong>SUMMARY</strong></p>

<p>The Grand Alliance implores Congress and the Federal Communications Commission to do everything possible to promote the rapid implementation of free over-the-air high-definition television (HDTV) and other digital Advanced Television (ATV) services. Specifically, Congress should reiterate its support for the plan the Commission and its Advisory Committee have pursued for almost a decade, including temporarily lending existing broadcasters a second 6 MHz channel during a transition period while the nation's consumers and broadcasters make the conversion to digital television. Congress should encourage the Commission to implement this ingenious plan as quickly as possible.</p>

<p>During the last eight years, through a unique combination of government leadership and private investment and competition, the U. S. has developed and thoroughly tested what is by far the world's best digital television system, dramatically leap-frogging earlier efforts in Japan and Europe to develop high-definition television. After investing half a billion dollars, with no government funding, U. S . industry now stands ready to deploy this fertile technology, giving not only breathtaking improvements in the video and audio quality of entertainment and news television, but also upgrading the nation's information infrastructure to enable the economical delivery of a host of useful information-age services that will help address pressing needs in education, health care, and other areas.</p>

<p>For many months now, dozens of U. S. manufacturing firms offering broadcast equipment, consumer electronics equipment, and integrated circuits have been poised to make the final investments in converting proven prototype technology into competitive commercial products. Capitalizing on this American technological triumph will create and preserve tens of thousands of high-skill, high-wage jobs, engendering substantial economic growth while improving the quality of life for Americans across all economic strata. At this crucial stage, Congress must reiterate its support for the FCC plan, providing the clear and consistent policy that is required to galvanize industry to make the further investments required to capitalize on the commanding U. S. technological lead. In stark contrast to what is needed, proposals in the Congress to auction the spectrum reserved for the conversion to ATV have put industry investment plans on hold and threaten to scuttle the conversion to digital television and to throw away the technological advantage and the potential for economic development that government and industry have fought so hard to achieve. Second-guessing the FCC's well-conceived plan at this late stage is causing significant delay and tremendous uncertainty, and these are anathema to potential investors in this new technology.</p>

<p>Rather than auction the ATV spectrum, a far better course would be 1) to do everything possible to hasten the conversion to digital television, 2) to repack the ATV channels more tightly once today's analog transmissions cease, and 3) to organize the recovered television spectrum into large, contiguous nationwide blocks that could support a wide variety of innovative wireless services. Such reorganized spectrum could be assigned using auctions, and would be far more valuable than the small, non contiguous slices of television spectrum to be used for the conversion to digital broadcast television. Such a course will enable free over-the-air television to compete in the years and decades to come, will vastly improve the efficiency of television spectrum use, will maximize the proceeds from spectrum auctions, and will create jobs and engender economic growth.</p>

<p>Good morning, Mr. Chairman and members of the Subcommittee. My name is Robert Graves and I am a technology and policy consultant representing the members of the digital HDTV Grand Alliance--AT&T, General Instrument, MIT, Philips Electronics, the David Sarnoff Research Center, Thomson Consumer Electronics, and Zenith Electronics--the partners who have worked together under the direction of the FCC's Advisory Committee on Advanced Television Service to develop the digital high-definition television system that is the basis of a new advanced television (AIV) standard the Advisory Committee has recommended to the Commission. I also serve as Chairman of the Advanced Television Systems Committee, an industry group of more than fifty corporations, associations and research institutions developing standards for digital television.</p>

<p>I'd like to speak today about one of our nation's greatest technological achievements-the development of all-digital high-definition television, offering pristine, theater-like, widescreen images and 6-channel CD-quality surround sound, and a whole lot more--a fundamental improvement in the National Information Infrastructure (NII) that can soon become widely available to all Americans. This achievement would never have been possible without a unique combination of consistent, bipartisan leadership from the Congress and the FCC, and private investment in a process that used first competition and later cooperation to yield by far the best digital television technology in the world--with proven performance that surpassed even the lofty expectations of its developers. Our plea today is that the Congress and the FCC see this ingenious plan through the last stages to a successful conclusion. Don't forsake this superb plan right when it's about to pay off for the American people.</p>

<p>In 1987, when the FCC began the process of defining an HDTV transmission standard, the United States was nowhere compared to Japan and Europe where decade-long, government-funded efforts were poised to deliver analog HDTV. However, with strong support from Congress and visionary leadership from the FCC and from former FCC Chairman Richard Wiley who was asked to lead the Commission's Advisory Committee, by 1993 the U.S. had leap-frogged over Japan and Europe into a preeminent position in the development of all-digital HDTV. From an original field of 23 different system proposals, the Advisory Committee selected four all-digital systems as finalists, and with encouragement from the Advisory Committee and the Commission, the proponents of these systems formed the Grand Alliance, agreeing to develop a single digital system that combined the best features of each competing system.</p>

<p>Although the Advisory Committee raised the performance bar substantially in specifying the requirements for the combined system, the Grand Alliance built a world-leading prototype system that cleared the bar with room to spare in exhaustive laboratory and field tests conducted last year. Given this stellar performance, last November the Advisory Committee recommended an ATV standard to the Commission based on the Grand Alliance system. Although no government funding was involved, this stunning collective achievement did not come free. Dozens of companies invested upwards of $500 million and devoted the best efforts of hundreds of volunteers in the Advisory Cornmittee process over almost a decade. The Grand Alliance members alone have invested approximately $300 million and some of their best engineering talent--at the expense of other opportunities--to get to this point.</p>

<p>An early pivotal decision by the Commission formed the basis for this successful effort. This was the simulcast approach whereby existing broadcasters would be given the temporary use of a second 6 MHz television channel to begin transmitting ATV signals while continuing to provide today's analog television transmissions on their existing frequencies. This was something of a rabbit out of a hat, because the extra channels to be used are the so-called taboo channels, channels like Channel 8 in Washington that are already allocated to television service, but can't be used for analog TV because of interference considerations. Digital transmission technology, however, produces signals that are much more resistant to noise and interference. One of the "miracles" of this technology is that digital television signals can be transmitted in these otherwise unusable channels and provide much higher resolution pictures (five times as much picture information) with an equal or better coverage area than analog television, using just one-sixteenth of the power at the transmitter. Thus, the Commission's simulcast decision gives broadcasters a practical means for making the transition to digital television without disenfranchising the owners of approximately 200 million analog television sets. The simulcast plan is an ingenious transition plan, not a "spectrum giveaway."</p>

<p>Another "miracle" of this technology is the tremendous flexibility that comes along for the ride in deploying a digital HDTV transmission system. The system uses a packetized data transport system with packet headers that identify the type of data that each packet carries. This means that in addition to HDTV, the transmission system can carry three or four simultaneous programs of standard-definition television (SDTV) at other times of the day, or numerous audio programs, software, stock quotes, sports scores, weather reports or a host of other potential information services (but not two-way services such as mobile radio communications). Thus, when consumers invest in HDTV they'll get dazzling pictures, stunning sound and a whole lot more. They'll get a high resolution display and a huge "piper into their homes whereby each TV channel could deliver 19 million bits of data per second--a speed about 1,000 times faster than today's typical computer modem. Their investment in entertainment television will provide an economical means for delivering information services that will address pressing needs in health care, education and other areas.</p>

<p>The Grand Alliance HDTV system puts the U. S. way out in front in the race to develop digital video technology--a key technology that will enable innovative multimedia applications beyond entertainment, including applications in education and training, medicine, business communications, and national defense. Getting a lead in broadcast entertainment and news is important, because the high volume production associated with consumer electronics will lower the costs for these other uses of HDTV.</p>

<p>No one is more excited than the Grand Alliance about the opportunities for flexible use of the ATV transmission system. Attached to this testimony are materials filed with the FCC that describe in greater detail the benefits that such flexible use by broadcasters can provide. But these materials also thoroughly explain our belief that the centerpiece application of the ATV channel will be and ought to be high-definition television. We're convinced that in the not too distant future, entertainment and news television will be viewed predominantly in HDTV. It will be just as unusual then to watch prime television programs in standard definition television as it would be to watch them in black-and-white today. The only real question is whether government policies will provide local, free over-the-air broadcasters an ability to upgrade their service to HDTV, or whether local broadcasters will have fallen by the wayside, unable to compete with technically superior HDTV services offered over cable, telephone and satellite facilities.</p>

<p>We believe that broadcasters must be able to offer HDTV if they are to remain competitive with other delivery media in the years and decades to come. This means lending each broadcaster a full 6 Liz channel during the transition period, because HDTV cannot be provided with anything less. And we believe that sticking with the FCC's long-standing plan--as reinforced in the new telecommunications act--to limit initial eligibility for these transition channels to existing broadcasters is the best course for preserving free, over-the-air television, for promoting the most rapid possible conversion to digital television, for using scarce spectrum resources most efficiently, and for maximizing the revenues that could flow to the US. Treasury from potential auctions of spectrum.</p>

<p>Two misconceptions have been heard frequently over the past few months regarding HDTV: first, that broadcasters have no interest in providing HDTV; and second, that consumers have no great desire for higher-resolution pictures. Neither of these ideas could be further from the truth. As shown in the attached documents filed with the FCC, the vast majority of broadcasters recognize that HDTV is essential to their survival and they plan to make HDTV the centerpiece application provided over the digital television channel. The attachments also describe consumer research demonstrating conclusively that consumers who have actually seen HDTV are prepared to pay a substantial premium, if necessary, for the dramatically improved performance it offers, and that substantial early sales will provide the volumes required to drive costs and consumer prices down rapidly. We've shown HDTV to thousands of people, and almost without exception they only want to know how soon they can buy a high-definition set. The members of the Grand Alliance, and many other firms in the industry, have already bet hundreds of millions and are prepared to invest many hundreds of millions more on our belief that HDTV will be a resounding success in the marketplace.</p>

<p>Regarding proposals to auction the ATV conversion channels, we recognize that the electromagnetic spectrum is an extremely valuable natural resource that belongs to the citizens of this country. And we believe that using auctions to assign spectrum to competing applicants is, generally speaking, a good idea. But auctioning these "loaner" channels planned for the conversion to digital TV is a bad idea--a bad public policy that would render broadcast ATV stillborn, undermine the ability of free over-the-air television to compete technically in the decades to come, lock in an inefficient usage of scarce spectrum, and grossly reduce the funds that ultimately could flow to the Treasury by auctioning recaptured television spectrum at the end of the transition.</p>

<p>In the first place, most of the discussion surrounding such auctions, and most of the wildly inflated estimates of its value, mistakenly assume that this spectrum can readily be used for almost any purpose. In fact, this spectrum is substantially encumbered by the need to protect surrounding analog television signals from interference. The "miracle" of shoe-horning in 1,600 additional TV channels only works for a low-power, digital, one-way, point-to-multipoint service where the digital and analog transmitters can be more or less co-located. This spectrum is not well-suited for two-way mobile communications, the application most frequently associated with the incredible estimates of its auction value. Indeed, without years of additional development and testing that would still result in a much less efficient use of these channels, these slivers of spectrum interspersed among existing analog television channels can only be used for one-way, digital, point-to-multipoint broadcast applications using portions of the Advisory Committee's proposed ATV standard or something very smaller.</p>

<p>So any sensible discussion of up-front auctions should assume that the channels would be used for television broadcasts or other data broadcast services using the proposed ATV standard, and the proceeds of such auctions would be far less than the inflated estimates commonly repeated. But even so, up-front auctions just don't make sense. Who would bid? The small, local broadcaster? Or does Congress want telephone companies or other large outside corporations to come in and outbid and supplant today's local broadcasters-just because they have more money?</p>

<p>And what about the future of "free" advertiser-supported TV? If these channels are auctioned, can we expect free TV? With auctions, digital television, especially HDTV, would develop as a geographically spotty, premium pay service rather than the ubiquitous, free service that Americans enjoy today. Free over-the-air TV is important to the cultural fabric of America, including our democratic political processes--both for the 35% of households who rely directly upon it and for the 65% who watch it predominantly even when it's carried over subscription services like cable. We should not give it up.</p>

<p>Even if Congress' only concern were maximizing the proceeds from spectrum auctions in order to help balance the budget, up-front auctions wouldn't make sense. After today's analog TV transmissions cease, the digital channels can be repacked much more tightly, leaving perhaps as much as 150 or even 200 MHz of recaptured spectrum that can be organized into large, nationwide contiguous blocks that could be used for a wide variety of wireless services, including two-way mobile radio services. This huge amount of unencumbered spectrum would be far more valuable than the small, non contiguous slices of ATV transition spectrum, and would yield far more--perhaps ten times more--than up-front auctions of today's taboo channels. Is our government so shortsighted that it can't see the value of a 10-times appreciation over ten years?</p>

<p>And auctioning the taboo channels now would mean never getting the analog channels back. It's one thing to reclaim one channel after lending existing broadcasters a second channel in order to enable a practical upgrade of their service, and quite another to confiscate their licenses, even if they're already withering away with an outmoded analog service. Up-front auctions would mean forgoing a unique opportunity to put in place a vastly more efficient TV spectrum plan and would lock in a grossly inefficient use of this scarce resource for decades to come.</p>

<p>And finally, up-front auctions, or even the continued threat of up-front auctions, would delay the introduction of HDTV generally, and render it stillborn for ubiquitous free over-the-air TV. This would squander the US. technological lead, eliminate jobs and reduce global competitiveness, and thwart the delivery of valuable services to consumers. The apparent willingness of some government policy makers to throw this all away is tremendously frustrating to those of us involved in developing the standard over the last decade.</p>

<p>Rather than up-front auctions of the ATV spectrum, Congress and the FCC should 1) do everything possible to hasten the conversion to digital television; 2) repack the ATV channels more tightly once today's analog transmissions cease; 3) organize the recovered television spectrum into large, contiguous nationwide blocks that could support a wide variety of innovative wireless services; and 4) assign the recovered, reorganized spectrum to competing applicants using auctions. Such a course will enable free over-the-air television to compete in the years and decades to come, will vastly improve the efficiency of television spectrum use, will maximize the funds that can flow to the Treasury from spectrum auctions, and will create and preserve jobs and engender economic growth.</p>

<p>Unlike proposals for up-front auctions, proposals for an "early give-back" of the analog channels have some merit. As with our recommended approach, in principle they would allow for a practical, but expeditious conversion to digital television, would greatly improve television's use of scarce spectrum resources, and would maximize the proceeds from auctions of recovered spectrum. However, the specific time periods recommended are too ambitious, especially for broadcasters in smaller markets.</p>

<p>In the attached documents filed with the FCC, the Grand Alliance argued that the current 15 year transition period included as part of the FCC plan can be reduced to twelve or even ten years. We also urged the FCC to set a nominal target date for the cessation of analog TV broadcasts, to evaluate progress along the way, and then to fix a final end of the transition period with three years advance notice of the final date for consumers and broadcasters. Proposals to auction channels after seven years with the spectrum actually relinquished after ten years appear on their face to be consistent with this time frame, however, they overlook several important factors.</p>

<p>First of all, the specific dates of 2002 and 2005 are not appropriate, because it's already 1996 and the standard has not yet been accepted by the FCC nor licenses assigned to broadcasters.</p>

<p>Moreover, the current FCC plan allows six years for broadcasters to apply for ATV licenses and to construct digital facilities. The ten-year transition period cannot begin until the standard is formally adopted, licenses are assigned, facilities are constructed, and ATV transmissions commence. Although we believe that most broadcasters will be able to be on the air with digital service in much less than six years, the smallest broadcasters may need that much time. Thus, although auctions of prospective recovered spectrum in theory could be held at any time, the earliest actual availability of the spectrum would probably be 2008 in the largest markets and up to five years later in the smaller markets, assuming that the proposed standard is adopted by the FCC and ATV licenses are assigned to existing broadcasters before the end of this year.</p>

<p>Although we believe the FCC can do much to promote a rapid transition and we're convinced that consumers will flock to digital television, especially HDTV, the ability to cease analog broadcasts will depend on the extent to which consumers who still rely exclusively on over-the-air broadcast television have invested in digital televisions or at least in converters that will allow them to view digital signals on their old analog TVs. In light of these uncertainties, we encourage Congress not to legislate a date certain for the return of analog TV frequencies, but rather to direct the FCC to do everything possible to hasten the conversion, and to authorize the Commission to assign recovered spectrum using auctions, on a market-by-market basis, if appropriate, including auctions prior to the actual availability of the spectrum.</p>

<p>In conclusion, our nation is now in the home stretch of an international digital video horse race with a three-length lead, with proven all-digital HDTV technology in hand that has leap-frogged earlier efforts in Japan and Europe--technology that will deliver quantum improvements in entertainment television and a host of other valuable services, while creating jobs and engendering economic growth. The fundamental government and industry planning are long since done, the pioneering technical work completed. And now with the finish line in sight, some government leaders have stopped the race while they debate the various virtuous contributions to society that horses can make. At this late stage, second-guessing of the FCC's well-conceived plan threatens to scuttle the whole process and throw away these hard-won benefits. Second-guessing by some members and even leaders of Congress, and quite frankly, by Chairman Hundt himself--alone among the five commissioners and alone among the four FCC Chairmen who have led this unparalleled bipartisan effort over nearly a decade--is causing significant delay and tremendous uncertainty, and these are anathema to potential investors in this new technology.</p>

<p>The Grand Alliance is confident that as more members of Congress fully understand this situation, Congress and the Commission will indeed show the will to win this race. We urge Congress to reiterate its support for the FCC plan upon which industry has relied over the last decade; and to direct the Commission to adopt an ATV standard, to issue ATV licenses to broadcasters, and to otherwise implement its ATV plan as expeditiously as possible in order to bring the benefits of this fertile technology to the American people. It would be tragically misguided and patently unfair, and would set a damaging precedent if the government were to jerk the rug out from under this splendid American success story.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 26, 2005  1:39 PM</b>
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
			<?=getComments(131)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 131)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1996_the_digital_hdtv_grand_alliancerobert_graves_commerce_committee_testamony.php" type="text/javascript" charset="utf-8"></script>
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