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
		AND e.entry_id = 94";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 94 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 94 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 94";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1990_vision.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 94";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1990 - Vision" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1990 - Vision" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1990 - Vision" />
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
	<title>HDTV Magazine - 1990 - Vision</title>
	<meta name="keywords" content="standard television, high resolution, television standards, old standard, new medium, hdtv, television, new, our, standard, visual, world, future, vision, business, been, must, good, home, images, high, things, better, enough, people" />
	<meta name="description" content="A shared vision, offers Peter M. Senge in his book, The Fifth Discipline, changes people's relationship with a company (group as well). It is no longer &quot;their company;&quot; it becomes &quot;our company.&quot; A shared vision is the first step in..." />
	<meta name="title" content="1990 - Vision" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1990 - Vision" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1990_vision.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="A shared vision, offers Peter M. Senge in his book, The Fifth Discipline, changes people's relationship with a company (group as well). It is no longer &quot;their company;&quot; it becomes &quot;our company.&quot; A shared vision is the first step in..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=94', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1990_vision.php">1990 - Vision</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 17, 2005</b>
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
				<p><em>A shared vision, offers Peter M. Senge in his book, The Fifth Discipline, changes people's relationship with a company (group as well). It is no longer "their company;" it becomes "our company." A shared vision is the first step in allowing people who mistrusted each other to begin to work together. It creates a common identity. In fact, an organization's shared sense of purpose, vision, and operating values establish the most basic level of commonality.... </em></p>

<p><em>Shared visions compel courage so naturally that people don't even realize the extent of their courage. Courage is simply doing whatever is needed in pursuit of the vision. In 1961. John Kennedy articulated a vision that had been emerging for many years among leaders within America's space program: to have a man on the moon by the end of the decade. This led to countless acts of courage and daring. </p>

<p>Senge's book reminds us also that a vision with no underlying sense of purpose, no calling, is just a good idea...all "sound and fury, signifying nothing."</em> _Peter M. Senge<br />
_________________________________</p>

<p>Japan gained a new global recognition and respect in the 60s and 70s from their HDTV initiatives. This recognition turned quickly into a nightmare as power struggles between industrialized nations arose in the 80s and 90s. Many unsteady nations sought to subdue the Japanese business aggression now being symbolized by their international lead in HDTV. No one could afford a doubt that HDTV would be anything other than a fantastic hit. </p>

<p>Being no match for the international Shenanigans of the more experienced Europeans what once looked to be a slam dunk for Japan became an international humiliation. They retrenched, worked hard at home again, and in the first years of the new millennium have crawled their way back into global HDTV prominence. </p>

<p>As a result of fights (technical obstructions) against the Japanese initiated standard for production much of the vibrant enthusiasm for HDTV was sucked out of the movement. Many at that time in the 80s thought and even declared that HDTV must fail--die on-the-vine--since it had no obvious economic starting point unless one international production standard could be agreed upon. Many thought that the HDTV consumer attraction could not overcome the established power of the old standards. </p>

<p>Despite setbacks in international diplomacy Japan went on to achieve a fine hour in communications history. It will be recorded that from Japan not only did a technology come alive but that nation perservered with persuasive leadership campaigns that led to the launch of HDTV. From one resolute decision made by Dr. Takio Fujio (then head of the world-class NHK Laboratories) back in the early 1960s the world of television changed for the good...forever. Japan would not let go of a vision with so much good in it and with steadfastness and at times questionable wisdom dedicated many billions of dollars to become ready for its global commercialization.</p>

<p>Once abounding with articles in the 70s and 80s the press abandoned HDTV in the 90s. It was a development that had taking too long, or so it seemed, and patience of the public voice was exhausted. But nothing could be hurried. Instead of remaining focused on the achievement that HDTV was to become the press featured the darker doings of entertainment and communications giants. Attention of press and public gravitated to the ever-green darling of technology-interactive TV. Regardless of the future for that true HDTV is destined to stand above all else as the giant among giants.  </p>

<p>A palatable vision heralding its coming has been trampled upon by those unable to profit quickly from it. In a frenzy of protective functions enemies, both public and secret, have distorted the commercialization with a conscious befuddling of the market. To offer a reminder of the promise of HDTV we have included a speech given in the fall of 1990 by Dale Cripps. The occasion was the closing ceremonies for the Osaka Flower and Industrial Festival--a major Japanese cultural and industrial exposition in Osaka, Japan. The Festival offered many applications and examples of HDTV technology--some industrial, others for the home. The speech was carried on Japanese television and radio, in Japanese newspapers and magazines. It is offered here as a reminder of why HDTV must soon again be the focal point of our communications efforts. HDTV is, according to Mr. Cripps, the only important communications format on the horizon that looks to enjoy a high-level payoff for its developers and for society in general. </p>

<p>Gary Reber, Publisher, Widescreen Review</p>

<p>******************************************</p>

<p>So much has been written and said about HDTV that one might think the world will stop if we must suffer on without it. While it may not be that important it most certainly is a marvelous thing to behold and to have in your business and home. More than anything else on the horizon it has the power to define an era where a new and higher standard of living begins in the home. </p>

<p>The Chinese have an old saying in their ancient book of changes, the I Ching. It declares that "all success is dependent on the effects of mutual attraction." If this remains so, the success of HDTV is guarantied. When, I ask, have you seen a more attractive item heading to market? It is not a painting, a tapestry, a statue, a rose, a tear, nor subtle smile, a wink, a laugh, but it is the most perfect visual representation of these things to reach the home. It is not a baseball game, a polo field, a ship at sea, a wrestling match, or a hockey game, but it brings the presence of these things to you as nothing else can. It is without qualification a brand new medium with even more uses than its fading predecessor--the NTSC, PAL and SECAM television standards. </p>

<p>Its powers of attraction must be great enough to overcome the price resistance of the conversion to it or this development will become an unmitigated disaster. It must remain as HDTV and not follow the temptation to become something else.</p>

<p>If entertainment is too frivolous a reason to rework the television infrastructure consider the changes rushing at us with ever-increasing speeds. A new era seems to be always on the doorstep. That is unsettling for many. Rapid change produces reactionary conditions. Fear of an approaching unknown is venomous. With HDTV in your business and home you remain safe behind this "windshield" fast-approaching change. The fears of the unknown are resolved by presenting it so clearly. Belonging to your own era can further our culture quicker with stability than when in a time of darkness and suffering shadowy communications. In the safe comfort of your home change for the good becomes appealing as it approaches and our adaptation comes with litttle delay. But why is it so different with HDTV over standard television?</p>

<p>I must leave you to answer that question. No person can say with unchallengable authority that HDTV will do any of that better than what existing TV systems do. Each must gain from a personal experience their own insight into the greater potential, the smarter value, the historical meaning of HDTV. Without that experience you are not really part of the movement to the future. Sorry to say that without a renewed vision heralded through HDTV you are not moving into the next era but rather stuck fast to the last one. Five times more visual information is delivered by HDTV each and every second over that in conventional television. When tallied up thatincrease, p over a lifetime can bring more to the individual and our grups than the imagination can presently conceive. What will the cumulative effects be over 20 years? Over a 100 years? And accumulated in the minds of 150 million people? Of 500 million people? Of 5 billion people? At a minimum our polulations will gain a new sense of the world if a new measure of it is presented to them. Having travled the around the world for 30 years I know that there is much more beauty in life than there are ugly scars. But without HDTV all televised imagery of the eart appears course. That courseness reaches us daily without the balance from beauty. Old standard TV is fine for relating the horrors of life but fails to convey the true qualities. With the old we are poorly served as a culture. </p>

<p>How can we judge, then, what economic impact this advance in home communications will have over the next 20 to 100 years? Advertisers will be asking this question soon. My personal belief is that they will find a very powerful tool for both creating and making a commercial impactful. I won't go so far as to call this a banaza maker, though it certainly could be. The social visonaries among us are seeing it as an instrument for expressing the higher nature of man. It undoubtedly works for both.</p>

<p>This last point truly distinguishes the renewed service promised with HDTV. It does if you believe subtleties play any part in enrichment, in enjoyment, understanding, and better decision making. Standard television filters out most of the informaton we associate with beauty. We are left with a less-attractive conclusion. We have been left looking at boulders rather than the shimmering light from diamonds.</p>

<p>How much more is this true for the frustrated artist who produces entertainment programming for standard television? I speak here again of the sublime visual subtleties that artists cannot use in the telling of their stories using stqndard television. The gross functions--car chases and crude violence or broad humor--that is all they have to work with. Sublime things are filtered away like cartoon drawings trying to immitate life. So sublime things are discarded from our mainstream values since no one can profit from producing and distributing them. Many never bother adjusting their old standard TV sets for this reason. What's the point of adjusting it if the program was made with that quality limitation in mind. Because of the all-important financial return from television, motion pictures have been produced with the visual limits of old standard television in mind. Our entire cultural vision has been filtered downward by old television standards. If its not "as seen on TV," its not seen. With HDTV the options for new story telling grow boundlessly with the new enriched visual/aural presentations in the home. HDTV opens new doors to human expression with its extended range of stimuli. It delivers the rose in all its delicate splindor just well as it does the excitement from a violent episode. Standard television does little more for the rose than suggest its shape. The range needed for full emotional communication is delivered with high-definition television. We are going to have better television, period. I think it is safe to say that that means a better life for those willing to acquire it.</p>

<p>What about the defining events of our times? Both acts of progress and destruction are better intepreted with this new medium. What we are, as a people, is presented unerringly by it. That which polishes and that which tarnishes our cultures is sure to be spotted at a greater distance than is possible with any other visual tool in our quiver. I see HDTV lending an accent to the good--a positive contributor to value bulding. I believe that our society will look upon HDTV as it might when discovering properly corrected lenses after fifty+ years of suffering from near-sightedness. Scoundrels take note...the days of your deception and illusions may be numbered as we embrace this new view.</p>

<p>This last point truly distinguishes the renewed service promised with HDTV. It does if you believe subtleties play any part in enrichment, in enjoyment, understanding, and better decision making. Standard television filters out most of the informaton we associate with beauty. We are left with a less-attractive conclusion. We have been left looking at boulders rather than the shimmering light from diamonds.</p>

<p>How much more is this true for the frustrated artist who produces entertainment programming for standard television? I speak here again of the sublime visual subtleties that artists cannot use in the telling of their stories using stqndard television. The gross functions--car chases and crude violence or broad humor--that is all they have to work with. Sublime things are filtered away like cartoon drawings trying to immitate life. So sublime things are discarded from our mainstream values since no one can profit from producing and distributing them. Many never bother adjusting their old standard TV sets for this reason. What's the point of adjusting it if the program was made with that quality limitation in mind. Because of the all-important financial return from television, motion pictures have been produced with the visual limits of old standard television in mind. Our entire cultural vision has been filtered downward by old television standards. If its not "as seen on TV," its not seen. With HDTV the options for new story telling grow boundlessly with the new enriched visual/aural presentations in the home. HDTV opens new doors to human expression with its extended range of stimuli. It delivers the rose in all its delicate splindor just well as it does the excitement from a violent episode. Standard television does little more for the rose than suggest its shape. The range needed for full emotional communication is delivered with high-definition television. We are going to have better television, period. I think it is safe to say that that means a better life for those willing to acquire it.</p>

<p>What about the defining events of our times? Both acts of progress and destruction are better intepreted with this new medium. What we are, as a people, is presented unerringly by it. That which polishes and that which tarnishes our cultures is sure to be spotted at a greater distance than is possible with any other visual tool in our quiver. I see HDTV lending an accent to the good--a positive contributor to value bulding. I believe that our society will look upon HDTV as it might when discovering properly corrected lenses after fifty+ years of suffering from near-sightedness. Scoundrels take note...the days of your deception and illusions may be numbered as we embrace this new view.</p>

<p>This last point truly distinguishes the renewed service promised with HDTV. It does if you believe subtleties play any part in enrichment, in enjoyment, understanding, and better decision making. Standard television filters out most of the informaton we associate with beauty. We are left with a less-attractive conclusion. We have been left looking at boulders rather than the shimmering light from diamonds.</p>

<p>One of the fastest changes coming to the world is the way we communicate in general. The written page is giving way rapidly to the visual icon. This is a form of 'communication compression,' where thesis is added upon thesis---not through tedious articles and books redescribing them--but rather distilled to within a single image representation. </p>

<p>This form of communication is particularly important where plateaus of understanding have been reached. You see this illustrated with familiar international road sign icons telling the "whole story" of what's ahead in just one picture. Further progress in future international cultural values can more easily be built upon with the greater use of such shorthand. In more complex matters both words and images will explain a new theme park concept, or a manufacturing plant to investors. Investments will increasingly result from the images of proposed future values, and these images must be good enough, clear enough, accurate enough so that the responsible managers of capital come rightly to feel confident in making their crucial investments for the environments of tomorrow. Tomorrow is all about vision--both externally, and internally.</p>

<p>Do I claim that the visual image has become the only agent for causing future investment? Of course not. Will high quality images and icons replace the spread sheet? No one says so. They will display it, however, in the clearest and most understandable way known to man. Today the best of business plans are presented with a dazzeling concoction of written documents, standard video, audio, and computer generated other images. With HDTV widely employed an entire business plan may be in the form of a finely honed audio-visual presentation, produced by desktop studios. Photographs, drawings, video images--the mltimedia panapoly--all delivered seemlessly to HDTV monitors and shown tantalizingly to large groups. Consumer-priced HDTV sets can shape the future by powerfully influencing economic decisions. Every other increase in communications facility has by magnitudes bolstered business volume. There is no reason to think that HDTV will do less. Companies, cities, states, and national secutiry has turned to the pictorialization of their future plans, and this will increasingly be done via high-resolution systems. And, when their projects are built, it's a safe bet they will have auditoriums where people can see the documentary about the project presented stunningly in an HDTV format. </p>

<p>It is being more realized today than ever before that the forests of the world are being consumed for our daily newspapers. Some have suggested the high resolution system in the home brings the finest of all solutions for this ecological dilemma. To be able to enjoy one's daily reading and graphic presentations by the television standards employed today is impossible. The present systems simply do not have enough readability. But with HDTV that problem is eliminated. We may have access to publications coming from any corner of the world. We should greet HDTV with unbridled enthusiasm if only considering these hopeful options.</p>

<p>One of the fastest changes coming to the world is the way we communicate in general. The written page is giving way rapidly to the visual icon. This is a form of 'communication compression,' where thesis is added upon thesis---not through tedious articles and books redescribing them--but rather distilled to within a single image representation. </p>

<p>This form of communication is particularly important where plateaus of understanding have been reached. You see this illustrated with familiar international road sign icons telling the "whole story" of what's ahead in just one picture. Further progress in future international cultural values can more easily be built upon with the greater use of such shorthand. In more complex matters both words and images will explain a new theme park concept, or a manufacturing plant to investors. Investments will increasingly result from the images of proposed future values, and these images must be good enough, clear enough, accurate enough so that the responsible managers of capital come rightly to feel confident in making their crucial investments for the environments of tomorrow. Tomorrow is all about vision--both externally, and internally.</p>

<p>Do I claim that the visual image has become the only agent for causing future investment? Of course not. Will high quality images and icons replace the spread sheet? No one says so. They will display it, however, in the clearest and most understandable way known to man. Today the best of business plans are presented with a dazzeling concoction of written documents, standard video, audio, and computer generated other images. With HDTV widely employed an entire business plan may be in the form of a finely honed audio-visual presentation, produced by desktop studios. Photographs, drawings, video images--the mltimedia panapoly--all delivered seemlessly to HDTV monitors and shown tantalizingly to large groups. Consumer-priced HDTV sets can shape the future by powerfully influencing economic decisions. Every other increase in communications facility has by magnitudes bolstered business volume. There is no reason to think that HDTV will do less. Companies, cities, states, and national secutiry has turned to the pictorialization of their future plans, and this will increasingly be done via high-resolution systems. And, when their projects are built, it's a safe bet they will have auditoriums where people can see the documentary about the project presented stunningly in an HDTV format. </p>

<p>It is being more realized today than ever before that the forests of the world are being consumed for our daily newspapers. Some have suggested the high resolution system in the home brings the finest of all solutions for this ecological dilemma. To be able to enjoy one's daily reading and graphic presentations by the television standards employed today is impossible. The present systems simply do not have enough readability. But with HDTV that problem is eliminated. We may have access to publications coming from any corner of the world. We should greet HDTV with unbridled enthusiasm if only considering these hopeful options.</p>

<p><br />
Not all teachers operate at the same level of efficiency. Some are truly outstanding while others merely get by. Perhaps someone here can think of what can be more important in the future than education? I cannot. When I think that our very best teachers have a classroom no more spacious than those of our very poorest, I believe the economy of modern distribution is underused, if not tortuously abused. We have the technology today to extend the best education from every field to all the classrooms and homes in the entire world--served by fully engaging advanced television systems. "Oh, that was tried before," you might say, "and it didn't work then." There are many things that will never work with NTSC. There are things that must be tried with HDTV. If the holding of student attention is achieved with high-definition, the very best teachers can become distributable to every student in the world at a fraction of the cost of employing the poorer ones. And those great teachers will be teaching well beyond their natural life span, preserved eternally in the new medium as fresh as the day they delivered their message. This noble experiment must be made to justify our claim of love for all future generations. There is no better means of creating good will in the future than providing a good education today. </p>

<p>Business will use HDTV. In the United States small business is the majority supplier to the nation's infrastructure. But small business has a difficult time in getting its products and services known to its potential customers. On average only 15% of a small company's market is reached before a product is technically obsolete. Business, especially the manufacturing segment, in the United States can and do benefit from using television to demonstrate their products, or tell of their services to potential customers. But it is an expensive distribution system that uses salespersons or the mails to carry the tapes to the customers. A wider, more cost effective distribution of business-to-business information should come soon under development. A company may reach 15 to 30% of its potential market in a single hour rather than 15% in two years. HDTV is destined for this grade of service if the view prevails that it is not expensive considering the powers of attraction it inherently has.</p>

<p>Medical imaging has been going on in large hospitals for some time. But where the need is greatest is in the outlying clinics. These clinics have not been able to afford the high resolution monitors which are large enough and clear enough to be of life-giving value. They are looking to the commercialization of HDTV to provide them the much needed lower cost higher performing displays. Beyond that, medical education is clearly enhanced by HDTV. Doctors who have experimented with this new medium have said there has never been demonstrated a more superior way to view an operation or display it for their students. Again, financial restraints become the factor in medical imaging. Where the price is lowered, the applications sprout anew.</p>

<p><strong>Tough Start </strong><br />
You have undoubtedly heard that HDTV is having a difficult time getting started. Take heart in the knowledge that every new product of worldwide significance passes through the same difficulty. The printing press was ridiculed as unworthy of the time being spent upon it by its inventor. After all, it was asked by the scribes, who could read in an illiterate world all the books just one press could print? Examine the beginnings of the telephone, radio, and television itself, to see the hardships experienced by those introducing them. In this regard HDTV is quite ordinary--consistent with the past--and all the usual obstructions will surely be overcome. So we must not be overly troubled with the obstructions to introducing anything as great as a brand new era, nor can we afford to be the least bit complacent about anything. As much as history testifies that good things do succeed, there are sad accounts filling libraries which show they can also fail. Complacency and indifference are at the root of most failures of good things.</p>

<p>There are some specifics which must be overcome in order to encourage the rest-of-the-world to advance with HDTV technologies and markets. The religious fervor, so valuable in evangelizing things, went out the HDTV movement the day two world production standards befell its fate. Two incompatible HDTV systems are not the mindset suitable to a new global era. It is a wasteful thing that painfully threatens this great development for humanity. Globally minded investors are not attracted to non-global, technically restricted enterprises. Regionally restricted television in a global era is a discord and can through us back...undermine the eager acceptance of HDTV. If I were king I would say to my engineers, "Fix it please, before the banquet begins." </p>

<p>To insure that HDTV opens up new visual industries we need to "see" a positive vision that says unashamedly that high-resolution systems are good for our cultural advancement. Each person will have to discover the reasons why for themselves. When leaders are aroused by a noble calling that impacts the whole of society, they find new energy, resources, and the resolve to master the situation, and advance like a well equipped army. </p>

<p>It has always looked like the manufacturers would need to fund the beginning and cause the first signals to seed interest in each of the markets. Sony, MEA, and Panasonic have already provided some funding to CBS, ABC, and NBC. One need not think, however, that these manufacturers are the sole economic beneficiaries of HDTV. Manufacturing companies will all change hands, managers, and locations before HDTV's day is done. The contemporary industrialist, governments, broadcasters, and shopkeepers will reap scant returns as compared to the wealth HDTV delivers to the populations of the world. That is absolutely true as far into the future as we dare look. If we don't lose sight of this, we will all act intuitively when we need to. In that we must trust.</p>

<p>Dale E. Cripps</p>

<p> </p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 17, 2005 10:47 PM</b>
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
			<?=getComments(94)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 94)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1990_vision.php" type="text/javascript" charset="utf-8"></script>
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