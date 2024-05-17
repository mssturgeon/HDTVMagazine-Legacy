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
		AND e.entry_id = 7";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 7 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 7 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 7";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 7";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Digital Television Is Our Gift To The Next Generation" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Digital Television Is Our Gift To The Next Generation" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Digital Television Is Our Gift To The Next Generation" />
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
	<title>HDTV Magazine - Digital Television Is Our Gift To The Next Generation</title>
	<meta name="keywords" content="high definition, digital high, local stations, consumer electronics, dtv transition, dtv, digital, our, broadcasters, analog, nab, transition, atsc, local, television, stations, congress, want, broadcast, consumers, year, fritts, work, been, consumer" />
	<meta name="description" content="A few weeks ago I initiated discussions with several prominent officials in the field of H/DTV. I wanted, and still do, to assemble a set of authorities who can make comments that require no speculation as to what is being said on policy and products. We certainly don't need obfuscation now when clarity is absolutely essential. We cannot forget that we are tearing apart old institutions along with the way we have interacted with them for more than 50 years. We are replacing it all with what is still unknown--the panoply of services potential in the digital age.

" />
	<meta name="title" content="Digital Television Is Our Gift To The Next Generation" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Digital Television Is Our Gift To The Next Generation" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="A few weeks ago I initiated discussions with several prominent officials in the field of H/DTV. I wanted, and still do, to assemble a set of authorities who can make comments that require no speculation as to what is being said on policy and products. We certainly don't need obfuscation now when clarity is absolutely essential. We cannot forget that we are tearing apart old institutions along with the way we have interacted with them for more than 50 years. We are replacing it all with what is still unknown--the panoply of services potential in the digital age.

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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=7', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php">Digital Television Is Our Gift To The Next Generation</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>May 11, 2005</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=12&category=Global & Worldview">Global & Worldview</a></b>
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
				<p>A few weeks ago I initiated discussions with several prominent officials in the field of H/DTV. I wanted, and still do, to assemble a set of authorities who can make comments that require no speculation as to what is being said on policy and products. We certainly don't need obfuscation now when clarity is absolutely essential. We cannot forget that we are tearing apart old institutions along with the way we have interacted with them for more than 50 years. We are replacing it all with what is still unknown--the panoply of services potential in the digital age.</p>

<p>I shot for the highest authorities in the land.</p>

<p>I wanted, and still do, those key members of the H/DTV community to participate in a family of BLOGS with the aim of using the authority each brings to end speculations and confusions, and, thus, hasten the transition <em>realistically</em>.</p>

<p>CEOs, I learned, never write BLOGS. They leave that to others. They make speeches and write proclamations. Those CEOs I asked to join our family, including the author of the article below (Edward Fritts of NAB), and Gary Shapiro, CEO of the Consumer Electronics Association in Washington, respectfully declined my specific invitation but they quickly opened new doors for far better communications with you. The mission I had in mind became mostly accomplished through the focus we can now bring upon their statesman's made recently in official settings--statements not typically made privy to the general public. Without the full power of public knowledge acting upon the HDTV movement, it must suffer. In consumer electronics both ignorance and confusion seal shut wallets and prevent good things from happening -- often for years, sometimes forever. We want to bring that era of confusion and shortage of public education about HDTV to an abrupt end. _Dale Cripps</p>

<p>Edward O. "Eddie" Fritts is the CEO of the Washington, D.C. based National Association of Broadcasters (NAB). He has earned a reputation for being one of the most effective lobbyist in the the nation's Capital. He is retiring from his post in a few months time but will remain active in a town he has mastered. There are issues in communications which face the nation and the world and we can trust that Fritts will be among those clearing the way for our future. He understands the business he is in and while we may have differed with some of his positions, we never lacked respect for them nor for <a href="http://www.hdtvmagazine.com/articles/articles-author.php?id=5">Eddie Fritts</a>.</p>

<p>The ATSC held a large annual meeting yesterday in Washington where Fritts spoke in what will undoubtedly be a series of farewell speeches. I bring it to you in its entirety since all of the issues as seen from the broadcast perspective are articulated. _Dale Cripps</p>

<p><br />
<span style="font-size:130%;"><strong><em>Remarks by Edward. O. Fritts, President & CEO National Association of Broadcasters to the </em></strong><strong><em>Advanced Television Systems Committee Annual Membership Meeting May 10, 2005</em></strong><br />
</span><br />
Good morning.</p>

<p>First, let me acknowledge <a href="http://www.wrf.com/directory.cfm?attorney_id=634">Dick Wiley's </a>contribution to the digital and high definition television transition. It's hard to believe it's the 10-year anniversary of the FCC Advisory Committee's recommendation to the FCC for adoption of the <a href="http://atsc.org">ATSC DTV </a>Standard. Dick chaired that committee, as you know. Thanks to his work and the work of all of you in this room, we have made remarkable progress this past decade.</p>

<p>You may have heard that I'll be stepping aside as NAB President later this year. It's been such an honor to represent local broadcasters in Washington for nearly 23 years. I want to thank Mark Richer, Robert Rast, the ATSC Board and ATSC members for facilitating a productive partnership with NAB.</p>

<p>NAB is proud to have helped found ATSC over two decades ago. You've achieved key milestones on technical standards issues and by getting all parties involved in valuable cross-industry discussions. It's vitally important to have neutral forums where competing interests can debate and hammer out consensus. ATSC is just such a forum, and your successes have benefited us all.<br />
Since last year's ATSC Annual Meeting, I'm aware that several standards and recommended practices have been completed. I'm speaking of the ATSC Recommended Practice on Receiver Performance, the ATSC PMCP Standard, the ATSC Software Download Service standard, and the completed standard on Enhanced VSB transmission.</p>

<p>All these accomplishments demonstrate the success that can be achieved when competing interests work together.</p>

<p>Your mission is our mission: to bring the next generation of television to American consumers, with as little disruption as possible, and to oversee the continued evolution of digital and high definition television. We never envisioned this process to be a sprint, but rather a marathon.</p>

<p>And look how far we've come!</p>

<p>1,500 DTV stations now on air in 211 markets. Ninety percent of TV households in markets with five or more DTV stations  70 percent of homes in markets with eight or more DTV broadcasters. Broadcast HDTV is ubiquitous - in primetime, in late night, all the major sporting events, and high-profile events like the Oscars.</p>

<p>Collectively, broadcasters have invested billions to make DTV a reality. Local stations have put budgets and businesses on the line, and are delivering on the promise of digital.</p>

<p>So from the NAB perspective, we are on target with DTV. Broadcasters have pretty much built out the system. The bottom line is that this is no longer a broadcaster DTV transition; it's now a consumer DTV transition.</p>

<p>You know, part of the job that I will miss most when I leave NAB is sparring with my colleagues at competing trade associations. It's no secret that over the years, we've had some DTV policy dust-ups with our friends at NCTA and CEA.</p>

<p>Despite our differences, I've got great respect for Gary Shapiro and Kyle McSlarrow, along with Kyle's predecessors, and the companies that they represent. Last month, Gary received an award at our NAB Convention in Las Vegas. I was struck by one of his comments which appeared in the trades. The reporter wrote, and I quote: "Shapiro hailed a long and distinguished cooperation with NAB on technical standards. Despite headline-grabbing differences, the truth is that together, we (meaning CEA and NAB) have changed the world." End quote.</p>

<p>Gary's right on that one - we have changed the world of television. Think back to the 1980s, when we launched this DTV journey together. Some of you remember Ed Markey's hearings when Congress was worried the Japanese analog HDTV system would trump American technology.</p>

<p>We took a delegation to Japan to get a better look at their system, which at the time was analog HD and satellite delivered only.</p>

<p>But over time, U.S. companies developed and perfected the unthinkable: digital high definition TV.</p>

<p>Suddenly, home-grown technology leap-frogged the competition with the finest television pictures the world has ever seen.</p>

<p>We knew the obstacles would be many, and the difficulties would be great. But we worked with Congress, the FCC, and many of you in this room, and we're now on our way to completing the overhaul of how television is delivered to American homes.</p>

<p>We need to understand where we started to appreciate the progress that has been made.<br />
I'm confident the public policy stage is set to ensure that broadcasting plays a vital part of the digital future. Pioneers like Bill Paley and Stanley Hubbard emerged as giants of analog TV; so too will a new generation of broadcasters seize the digital world.</p>

<p>Just last month, Verizon CEO Ivan Seidenberg came to NAB's convention looking for partnership opportunities with local broadcasters. In my mind, that speaks volumes about the value of local television. Verizon and other phone companies know that broadcasters not only have a franchise on localism, but that broadcasting is still the "gathering place" for a nation.<br />
Local TV stations are expanding broadcasting's footprint on the Internet. Soon, our programming will be on cellphones and a multitude of other wireless devices, which bodes well for our future, too.</p>

<p>Admittedly, there have been twists and turns on the road to digital. And even a detour or two - last week's court decision overturning the broadcast flag was disappointing, and we'll work to correct that.</p>

<p>Some of us also get frustrated by the DTV myths. One myth is that broadcasters are profiteering off of DTV spectrum. Instead, the reality is that broadcasters have billions of dollars of stranded capital in this transition. At some point, there may be revenue generated by DTV; up to now, however, it has been a considerable cash drain on most local stations.</p>

<p>I get a chuckle from my cable friends when they claim they've spent $90 billion or $100 billion "investing" in the DTV transition. We all know that money is coming straight from consumers, who year in and year watch their cable bills rising 3 to 6 times the rate of inflation.</p>

<p>Broadcasters, of course, don't have the luxury of charging monthly fees. Our service is free to end users. Yet despite the huge cash outlays and enormous challenges, we have embraced the move to digital. We realize that local stations cannot remain competitive as analog players in a digital world.</p>

<p>The second big myth is that broadcasters want to hold on to analog spectrum indefinitely.<br />
Why would we want to do that? Why would stations want to continue paying tens of thousands of dollars in extra utility bills each year to send two signals -- both analog and digital?</p>

<p>The fact is broadcasters don't want to send two signals any longer than is necessary. There is no incentive whatsoever for local stations to want this transition to go on indefinitely.</p>

<p>Analog television will end, to be sure. The question for policymakers is when, and how do you not disenfranchise millions of consumers in the process.</p>

<p>We agree with those in Congress who warn of the potential for consumer outrage if this transition is not handled carefully. We should be mindful of the quote from Elliot Engle, a House telecom subcommittee member from New York. "If members of Congress turn off analog TV in 2006," he said, "we can all expect to be impeached in 2007."</p>

<p>We understand Congress's need for additional revenue from spectrum auctions. But it's noteworthy that the Congressional Budget Office now says that Congress will generate more revenue from spectrum auctions that are held later, not sooner.</p>

<p>Let me repeat that for added emphasis: The highly-respected, non-partisan Congressional Budget Office says analog TV auctions will generate more money for the Treasury if the auctions are held later, not sooner. So we're ready to roll up our sleeves and work with Congress on sensible DTV legislation.</p>

<p>Our priorities are straightforward:</p>

<p><strong>One:</strong> Deadlines that protect millions of Americans from losing access to local broadcasting;</p>

<p><strong>Two:</strong> Access to consumers for broadcast DTV programming carried on cable. Digital and high-definition TV is about consumers having more choice and better quality. Cable gatekeepers like Comcast and Time Warner ought not be allowed to deny consumers access to any broadcast digital programming. All free bits must flow to the consumer;</p>

<p><strong>Three:</strong> No cable headend down-conversion of broadcast programming from digital to analog;<br />
And Four: broadcast flag protection to ensure that high-quality programming not migrate away from free TV.</p>

<p><strong>Finally,</strong> I can't resist commenting on CEA's request to delay DTV tuner mandate rules. Let me say it again: this transition lets TV set makers share billions of dollars in the greatest transference of wealth in consumer electronics history.</p>

<p>If we're talking about ending analog TV, it makes no sense for manufacturers to flood the market this Christmas with millions of analog TV sets. That only elongates the transition.<br />
Rather than seeking delays in the tuner mandate, shouldn't we instead be labeling analog TV sets "soon to be obsolete?"</p>

<p>Last year at this meeting, I said that "DTV represents nothing short of a re-birth of over-the-air broadcasting. We think manufacturers should want over-the-air reception to be a principal feature in DTV sets, and to promote it as a primary, value-added feature. Consumers will appreciate the value of DTV broadcasts and the advantages of over-the-air reception compared with other distribution channels." That's still true.</p>

<p>In closing, let me reiterate NAB's willingness to work with lawmakers on this difficult issue. Our viewers are constituents of every member of Congress, and we need to be mindful that Americans have a timeless bond with local TV stations.</p>

<p>My friends, it's been a privilege for me to play a small role in this historic transition. Digital TV is our gift to the next generation. It is surely as important as the transition from black and white to color. Thank you, ATSC members, for your vision and guidance in this process, and thanks for your partnership with NAB these many years.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>May 11, 2005  9:42 AM</b>
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
			<?=getComments(7)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 7)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/05/digital-television-is-our-gift-to-the-next-generation.php" type="text/javascript" charset="utf-8"></script>
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