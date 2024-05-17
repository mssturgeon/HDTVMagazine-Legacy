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
		AND e.entry_id = 103";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 103 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 103 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 103";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2005/06/the-politics-of-the-transition-to-dtv-jeff-hart.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 103";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download The Politics of the Transition to DTV - Jeff Hart" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="The Politics of the Transition to DTV - Jeff Hart" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="The Politics of the Transition to DTV - Jeff Hart" />
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
	<title>HDTV Magazine - The Politics of the Transition to DTV - Jeff Hart</title>
	<meta name="keywords" content="cable operators, dtv tuners, intellectual property, air broadcasters, cable satellite, dtv, cable, digital, fcc, local, consumers, signals, broadcasters, transition, new, services, rights, analog, quality, set, content, service, equipment, decisions, broadcasting" />
	<meta name="description" content="The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing nations. I will focus today on the transition in the United States as well as discuss the experience of other countries where that helps us to understand the choices available.

Here are the key policy issues in making the transition: 

&lt;strong&gt;&lt;em&gt;- subsidizing poor and elderly consumers so that the analog broadcasts can be turned off (thus freeing spectrum for other uses); &lt;/em&gt;&lt;/strong&gt;

&lt;em&gt;&lt;strong&gt;- working out the relationships between over-the-air broadcasters on one hand and cable and satellite service providers on the other via &quot;must carry&quot; rules in a fair and equitable manner; &lt;/strong&gt;&lt;/em&gt;

&lt;em&gt;&lt;strong&gt;- allowing consumers to purchase add-on services without being forced to purchase unnecessary equipment from service providers (&quot;plug and play&quot;);&lt;/strong&gt;&lt;/em&gt;

&lt;strong&gt;&lt;em&gt;- protecting the intellectual property rights of content producers without violating the rights of consumers to engage in &quot;fair use&quot; of content; and&lt;/em&gt;&lt;/strong&gt;

&lt;strong&gt;&lt;em&gt;- maintaining the important role of local broadcasters &lt;/em&gt;&lt;/strong&gt;in providing local political information to citizens. 

&lt;strong&gt;Short Description&lt;/strong&gt;
" />
	<meta name="title" content="The Politics of the Transition to DTV - Jeff Hart" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="The Politics of the Transition to DTV - Jeff Hart" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2005/06/the-politics-of-the-transition-to-dtv-jeff-hart.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing nations. I will focus today on the transition in the United States as well as discuss the experience of other countries where that helps us to understand the choices available.

Here are the key policy issues in making the transition: 

&lt;strong&gt;&lt;em&gt;- subsidizing poor and elderly consumers so that the analog broadcasts can be turned off (thus freeing spectrum for other uses); &lt;/em&gt;&lt;/strong&gt;

&lt;em&gt;&lt;strong&gt;- working out the relationships between over-the-air broadcasters on one hand and cable and satellite service providers on the other via &quot;must carry&quot; rules in a fair and equitable manner; &lt;/strong&gt;&lt;/em&gt;

&lt;em&gt;&lt;strong&gt;- allowing consumers to purchase add-on services without being forced to purchase unnecessary equipment from service providers (&quot;plug and play&quot;);&lt;/strong&gt;&lt;/em&gt;

&lt;strong&gt;&lt;em&gt;- protecting the intellectual property rights of content producers without violating the rights of consumers to engage in &quot;fair use&quot; of content; and&lt;/em&gt;&lt;/strong&gt;

&lt;strong&gt;&lt;em&gt;- maintaining the important role of local broadcasters &lt;/em&gt;&lt;/strong&gt;in providing local political information to citizens. 

&lt;strong&gt;Short Description&lt;/strong&gt;
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=103', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2005/06/the-politics-of-the-transition-to-dtv-jeff-hart.php">The Politics of the Transition to DTV - Jeff Hart</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Dale Cripps</b> on <b>June 20, 2005</b>
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
				<p>Few have been as academically prepared to understand the political dynamite encased in the HDTV movement as Dr. Jeffery Hart. I am proud to count this distinguished educator/author among my friends for the last two decades. I was especially delighted to receive his permission to publish a paper he prepared for the Technology Conference, University of California, Berkeley, California which was held earlier this year.  </p>

<p>For those of you who are just joining this "party" you may find all of the machinations about HDTV beyond comprehension. Jeff uses straight forward language to help you become a more educated electorate. There are things going on that you should know about and have a say in, but without understanding the story that makes up this move to a new communications era you quickly discover that you are far from being qualified to cast your vote, much less spend your money. All of the things I bring to you here in these columns (please check out the History link as well) are designed to give you a background so you too may feel a part of this historic move to what some insists is going to be a better world. His talk is aptly entitled:</p>

<p><strong>"The Politics of the Transition to Digital Television"</strong></p>

<p>The arguments to which he gives lucid form frame the debates raging today as the political will of the nation struggles to craft and pass legislation (Senators John McCain R-AZ, Joseph Liberman D-CT -- "Save Lives Act of 2005"). The goal of this legislation is to complete the DTV terrestrial transition as quickly as possible and reassign the digital spectrum for new uses, one being homeland security communications. The concluding sentence of Jeff's work is particularly important for all to consider as a requirement for completing this transition._Dale Cripps</p>

<p><br />
by<br />
Jeffrey A. Hart<br />
  </p>

<p> <br />
 </p>

<p><strong>The Politics of the Transition to Digital Television</strong> </p>

<p>The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing countries. I will focus on the transition in the United States as well as discuss the experience of other countries where that helps us to understand the choices available.</p>

<p>I will address first the key policy issues in making the transition: </p>

<p><strong><em>-subsidizing poor and elderly consumers so that the analog broadcasts can be turned off (thus freeing spectrum for other uses); </em></strong></p>

<p><em><strong>-working out the relationships between over-the-air broadcasters on one hand and cable and satellite service providers on the other via "must carry" rules in a fair and equitable manner; </strong></em></p>

<p><em><strong>-allowing consumers to purchase add-on services without being forced to purchase unnecessary equipment from service providers ("plug and play");</strong></em></p>

<p><strong><em>-protecting the intellectual property rights of content producers without violating the rights of consumers to engage in "fair use" of content; and</em></strong></p>

<p><strong><em>-maintaining the important role of local broadcasters </em></strong>in providing local political information to citizens. </p>

<p><strong>Short Description</strong></p>

<p>  <br />
<strong>Introduction</strong><br />
The transition to digital television (DTV) is occurring in all the major industrialized countries and in a selected number of developing countries. I want to focus today on the transition in the United States, but I will also discuss the experience of other countries where that helps us to understand the choices available. The U.S. case is important not just because of the size of the U.S. economy but also because of the leadership of U.S. firms in global markets.  The distinctive features of business-government relations in the United States have been a key determinant of U.S. policy choices for DTV. The dominance of broadcasting and other forms of TV signal delivery by privately owned firms is probably the most important difference, but there are others. The tradition of regulation by a quasi-autonomous government agency, the Federal Communication Commission (FCC), distinguishes the U.S. transition from those in Europe and Asia.   </p>

<p>One key result is that the interests of U.S. electronics firms and consumers were taken into account earlier than in other regions. The United States was the first to opt for all-digital as opposed to hybrid digital-analog standards. The U.S. government, unlike those in Europe and Japan, did not support standards put forward by a coalition of consumer electronics manufacturers and broadcasters.  U.S. regulatory institutions were sensitive to a number of issues that were until ignored elsewhere, such as the cost to consumers of purchasing new equipment and the need to promote continued innovation in digital technologies. On the negative side, however, the final U.S. government decisions on DTV standards produced considerable confusion on the part of manufacturers, broadcasters, and consumers, a confusion that can be observed directly by anyone attempting to buy new DTV equipment and services at their local electronics outlets.  Coping with that confusion and dealing with the inability or reluctance of some customers to pay for new DTV equipment has become the key challenge of completing the transition to digital television in the United States.</p>

<p><strong>Coping with Confusion</strong><br />
The key DTV decisions by the FCC in the 1990s guaranteed that there would be confusion in the marketplace of DTV equipment and services.  No specific format for encoding or delivering DTV signals over the air was mandated.  Broadcasters and manufacturers were left to figure out what types of signals customers would be willing to pay for at premium DTV prices. So, for example, some over-the-air broadcasters decided not to use their DTV channels to broadcast in high definition. Instead they experimented with multicasting: i.e. the use of a single TV channel to broadcast a number of standard definition TV signals. This means that the broadcaster was using the allocated spectrum to become a sort of mini-cable operator. The bet was that the customer would be willing to pay for more choice in programming (but not for higher picture quality).</p>

<p>Other over-the-air broadcasters were betting that customers would be willing to pay for better picture quality, but they disagreed on what quality increment was required. The standards debates leading up the FCC decisions of the 1990s identified a range of choices for picture and signal formats. The ones that emerged with substantial corporate backing were 480p, 720p, 1080i and 1080p. The number in the number/letter combination stands for the number of scanning lines per image. The small letter p stands for progressive scanning; the small letter i stands for interlaced scanning. Interlaced scanning involves the sending of every other line in an image in one burst followed by the sending of the rest of the lines in the next burst and so on.  Interlacing was invented in the early days of monochrome TV broadcasting to conserve spectrum. All standard definition televisions use interlacing. Progressive scanning involves the sending of all the lines in an image in one burst (not two). All computer monitors, unlike standard definition TVs, use progressive scanning. While progressive scanning is less conserving of spectrum, it has the advantage of eliminating certain visual artefacts in the final image like "flicker."  Progressive scanning is better for the display of text information than interlacing.</p>

<p>480p provides a progressively scanned digital version of a standard definition TV image. It is the cheapest to provide but does not provide as large an increment in picture quality as the other alternatives. 480p is the format of choice for broadcasters who chose the multicasting option.</p>

<p>720p provides a higher quality image than 480p and possibly as high image quality as 1080i because it is progressive. ABC, NBC, and their affiliates opted for 720p and made major investments in production facilities for broadcasting in this format. They focused initially on converting broadcasts of sporting events to 720p.</p>

<p>1080i was the choice of CBS and its affiliates because of their strong belief that 720p did not provide a high enough quality increment over standard definition analog TV to make consumers willing to pay the premium for DTV signals.  Their preference for interlacing was partly the result of the relationship between CBS and Sony, in which the latter provided 1080i equipment to the former.  CBS had allies also in the film industry, including Sony Pictures (formerly Columbia Pictures), who swore by 1080i as a better format in which to view movies.</p>

<p>1080p had the least support of the main alternative formats because it was the most expensive to produce and display. Some of the technological components necessary to produce content in that format were still not widely available in 2005. Nevertheless, all the chips that were in ATSC-compatible HDTV tuners (I will call them DTV tuners for short) were capable of decoding 1080p images and so some companies were betting that the higher picture quality of 1080p would eventually triumph over the other alternatives.</p>

<p>To deal with the diversity of signal formats the FCC mandated in 2002 the progressive phasing in of TV sets with DTV tuners, requiring that new sets with a given screen size or larger contain tuners.  Here are the specific phase-in requirements: </p>

<p>Receivers with screen sizes 36 inches and above -- 50% of a responsible party's units must include DTV tuners effective July 1, 2004; 100% of such units must include DTV tuners effective July 1, 2005. Receivers with screen sizes 25 to 35 inches -- 50% of a responsible party's units must include DTV tuners effective July 1, 2005; 100% of such units must include DTV tuners effective July 1, 2006. Receivers with screen sizes 13 to 24 inches -- 100% of all such units must include DTV tuners effective July 1, 2007. <br />
By the year 2007, therefore, all new TV sets with 13-inch screens or larger would be required to have DTV tuners. (<a href="http://www.dtg.org.uk/news/news.php?class=countries&subclass=0&id=933">this has sense been accelerated</a>). </p>

<p>In the meantime, consumers would continue to have to cope with complexity in stores where labelling of DTV sets and equipment includes such unfamiliar terms as HDTV-ready, HDTV-capable, HDTV-compatible, and HDTV-upgradeable. The sets themselves came in the following technological varieties: CRT (direct view), CRT-based projection, LCD flat panel, LCD projection, DLP projection, and LCOS projection (I won't bother to explain the acronyms here). On the back and there were the following kinds of "secure" DTV connectors: DVI, HDMI, and Broadcast Flag (more on this later). There were also a variety of connectors for antennas, VCRs, DVDs, DVRs, set-top boxes, and other such devices. Customers would be asked if they wanted to get their signal over the air, or via cable or satellite. If customers wanted to connect a DTV to a Windows Media Center personal computer, they would be in another vast new world of acronym-filled complexity. For the fanatics and insanely rich, there was the world of the "home theater" to master. The rich would simply pay someone who knew enough about all this stuff to do it for them, but then they were left with the problem of figuring out how to make it all work the way it was supposed to.</p>

<p><strong>Turning Off Analog</strong><br />
The FCC DTV decisions of the 1990s resulted in the loaning of a second channel to over-the-air broadcasters to use for converting to digital broadcasting while continuing to provide analog services.  The FCC's idea was that once the digital transition was complete the analog channels would be returned to the government to dispose of as needed. The return of spectrum would permit the FCC to auction it off to the highest bidder, so the government had a strong incentive to get back all those old analog TV channels as soon as possible. The revenues from auctions were already being included in estimates of future government revenues during the Clinton Administration, so key members of the government were eager to push for the rapid completion of the digital transition.  </p>

<p>The problem was that the FCC and Congress had recognized that the analog signals should not be shut off until a good percentage of consumers were receiving or at least were able to receive digital  broadcasts. In 1997 when the DTV transition plan was launched, Congress passed a "sense of Congress" resolution as part of an intelligence reform act that stipulated that the spectrum would be returned on December 31, 2006, but only if 85 percent of the residents of any given local community had the necessary equipment to display digital signals.The interpretation of this somewhat vague rule would be left to the FCC.</p>

<p>Less than three percent of American homes had sets capable of decoding DTV signals as of early 2005 although a much larger percentage, perhaps more than 80 percent, received TV signals in digital formats from either cable or satellite services and the 2006 deadline was fast approaching. The sales of such sets were growing rapidly, especially as lower cost DTVs started to be featured in the major consumer stores. The number of cable and satellite services offering HDTV-quality signals was also growing rapidly. In 2006, the prices of flat panel plasma TVs were expected to continue to descend below the current average price of around $2,000, especially as the larger LCD TVs also were expected to decline in price from the current $2,000 average to around $1,000. Nevertheless, it was unlikely that 85 percent or more of the households in more than a handful of communities would own TVs with DTV tuners by the end of 2006</p>

<p>An additional problem, highlighted by outgoing FCC Commissioner Michael Powell was that many households possessed more than one TV, but were not likely to be receiving digital signals on every set they own.  Also, a number of over-the-air broadcasters failed to comply with FCC orders to begin broadcasting in DTV formats, so households with DTV sets in those localities but without cable or satellite services would obviously not be able to contribute to meeting the 85 percent goal.</p>

<p>As a result, the FCC, in its desire to get the spectrum back sooner rather than later, proposed a new deadline of 2009 and an easier test of the ability of households to decode DTV signals: i.e., that the use of cable or satellite services where the service provides a digital signal either to a set-top box, or, even less ambitiously, to a nearby connection point, would count toward the 85 percent goal.  If the household opted not to purchase a DTV set, therefore, it might still enjoy TV broadcasts if it either purchased or was given a box to convert the DTV signal to a standard definition analog signal. All cable subscribers qualified as DTV-ready households by that standard. Problem of rapid transition solved!</p>

<p>That proposal, engineered by Kenneth Ferree, the chief of the FCC's Media Bureau, in January 2005, but had not been approved as of February 2005. Ferree left the FCC soon after making the proposal. The plan was strongly opposed by the National Association of Broadcasters, whose members were not in a hurry to return their analog channels to the federal government. They claimed that to meet the 85 percent goal, 73 million sets not connected to a cable or satellite service would have to be fitted with a converter at an estimated cost of around $300 per unit, at a total estimated cost of $22 billion. It should not come as a surprise that the $300 price tag given by the NAB was contested. Motorola Corporation, for example estimated the boxes could be produced in high volume for between $50 and $75 per unit. Motorola and other electronics manufacturers like Intel were interested in seeing the analog spectrum returned and auctioned off for new wireless uses. </p>

<p>The important underlying issue, however, was that the shutting off of the analog signals would greatly inconvenience millions of TV watchers who either could not afford or were not willing to purchase the necessary converters and therefore raised the question of whether there needed to be government subsidies to allow these individuals to continue using their analog equipment.   </p>

<p><strong>Must Carry</strong><br />
Another difficult question was how to set the rules for the relationships between over-the-air broadcasters and cable and satellite service providers during and after the transition. Cable operators were bound by "must carry" rules that impelled them to give their customers access to the analog signals of local over-the-air broadcasters via the cable service. The cable operators did not get paid for this service, even though the local broadcasters continued to receive advertising revenues based on the audience (cable plus non-cable) that their signal could reach. This really irritated the cable operators so they looked for ways to get compensated for carrying the signals of local broadcasters on increasingly scarce cable bandwidth. No such must carry rules governed the relationship between local over-the-air broadcasters and satellite service providers.</p>

<p><strong>Cable operators</strong> - led by Ted Turner initially - challenged the "must carry" rules on Constitutional grounds as a violation of their right to free speech, but ultimately lost this battle in the Supreme Court. They insisted that they could not be forced to carry digital signals the way they had been forced to carry analog ones, especially multicasts because this violated the intention of policy makers to promote a higher quality of broadcasts not simply a proliferation of channels. They wanted over-the-air broadcasters and cable network programmers to compete on an equal basis for cable bandwidth and obviously to pay for carriage and they wanted local cable operators to have full control over the programming packages offered to cable customers in their service area. Cable companies particularly objected to efforts of broadcasters to get compensation for providing DTV signals for carriage by cable operators (especially HDTV coverage of popular sporting  events).  A spokesman for Time Warner Cable, Keith Cocozza said "The issue at heart is that broadcasters are trying to insist that they are compensated for something that they get from the government for free." </p>

<p>What the local broadcasters wanted was for both cable and satellite to be bound by "must carry" rules for digital signals, especially those who had already investing in multicast technology (e.g. Belo. They also wanted the cable operators to pay them for carrying their content on cable networks.  The DTV decisions of the 1990s gave the local broadcasters the right to use their digital channel either for HDTV or for other purposes including the broadcasting of multiple standard definition signals (multicasting). Some broadcasting networks opted for multicasting, thus defining the choice for their local affiliates. The problem was that the cable companies did not want to carry the multicasts which they saw as direct competition and wanted to be compensated for carrying whatever they decided to carry. In short, disagreements over these matters were blocking cable carriage not just about multicasts but also of local- and network-produced HDTV-quality digital signals. Consumers who purchased DTVs to view this content were disappointed.</p>

<p><strong>Plug and Play</strong><br />
Related closely to the must carry controversy was the question of what sorts of equipment consumers had to purchase or rent from cable operators in order to display DTV signals on their televisions.  The decision of the FCC to mandate the inclusion of DTV tuners in new televisions meant that after 2007 it would not be necessary to include DTV tuners in the set-top boxes sold or rented to cable subscribers.  Nevertheless, the cable operators continued to insist that they had the right to sell or rent set-top boxes because of the interactive (two-way) services they wanted to provide -- such as pay per view, virtual digital video recorders, or electronic program guides -- that went beyond the one-way service of decoding DTV signals.  </p>

<p>In the interest of saving consumers unnecessary expense and clutter, the FCC ordered in October 2003 that televisions that were "Digital Cable Ready" should be labelled as such and that the two stakeholders (set manufacturers and cable operators) should work together to ensure that televisions so labelled would be compatible with cable services and equipment. The Consumer Electronics Association (CEA) and the National Cable Television Association (NCTA) had issued a Memorandum of Understanding in December 2002 calling for a "plug and play" format for one-way signals from cable to DTV sets. Thus, to some extent, the later FCC order was an endorsement of the earlier CEA/NCTA agreement and a plea for further negotiations. The two industries were urged to go beyond the one-way plug and play agreement to negotiate a similar one for two-way interactive services. Such an agreement was still under negotiation in February 2005.</p>

<p>One of the near-term consequences of the Digital Cable Ready Order of 2003 was the development of the CableCard system. CableCard is a card-shaped object that plugs into a socket in a Digital Cable Ready TV that gives the consumer access to the cable services of a specific cable provider.  The primary function of the CableCard is to assure that only paying customers get access, but a secondary and quite valuable function is to do this in a way that does not require the purchase or rental of a set-top box with a redundant DTV tuner.</p>

<p>The CableCard system was similar to one developed for the DVB standard in Western Europe. From the consumer standpoint, not having to have multiple set-top boxes when subscribing to more than one service or to buy or rent a new box when changing services made a lot of sense. This decision, in short, assured that there would be lower switching costs for consumers and lower barriers to entry for potential competitors in local DTV cable service markets.</p>

<p>The cable operators resisted the CableCard initially because they thought it would reduce their ability to realize the revenues associated with proprietary features they planned to build into their next-generation set-top boxes. The set manufacturers were worried that the increased cost of including a DTV tuner in sets would have to be passed along to consumers in the form of higher prices and that higher prices would deter DTV sales.  Another disadvantage mentioned by critics of the CableCard decision was that equipment purchased before the decision, like digital video recorders, might not work with Digital Cable Ready televisions.  These sorts of timing and incompatibility issues came up also in the area of intellectual property protection devices (see below). The FCC held firm on both the Digital Cable Ready and Plug and Play decisions, however, and both set manufacturers and cable operators began to plan their next moves accordingly.</p>

<p><strong>Intellectual Property Protections vs. Fair Use</strong><br />
The preceding sections dealt with a number of regulatory decisions that were motivated at least partly by concerns about how to ensure continued technological innovation in the wake of the DTV decisions of the 1990s. This was not an idle concern. One of the unfortunate potential impacts of major standards decisions was to freeze technological development, even when that may not be in the best interests of society. The FCC frequently justified its standards decisions in terms of the need to guarantee that there would continue to be competitive markets.  In their view, competition was the best way to ensure continued innovation in technologies. Nevertheless, the agency also recognized that technical standards sometimes were needed to reduce confusion among consumers and producers and that there needed to be regulatory intervention occasionally to reduce the tendency of different stakeholders to squabble among themselves, thus holding back the development of the market. The FCC led by Michael Powell was particularly focused on stopping this sort of infighting.</p>

<p>Unfortunately, decisions made on other issues might eventually reduce the scope for technological innovation precisely because they are designed to protect intellectual property rights of a certain set of rights owners, in this case the film, TV content, and recorded music industries, at the expense of consumer rights to fair use.</p>

<p>Intellectual property rights are granted to ensure that creative people will be adequately compensated for their creativity and so that the fruits of their creativity will be enjoyed by all. The main method used to accomplish this end is to grant a temporary monopoly of usage rights for a growing list of products and services that embody individual creativity: books, movies, recorded music, chemical formulas of new pharmaceuticals, etc.  The legal system of intellectual property rights permits the rights holders to obtain compensation not just for the direct sale of the resulting products and services but also for licensing others to commercialize those products and services.  </p>

<p>There are separate intellectual property rights (IPR) regimes intended to protect different types of creative activity.  The patent regime protects both innovative products and manufacturing processes. The copyright regime protects literary creativity and other forms of recorded performance and/or storytelling.  The semiconductor mask protection act protects integrated circuit designs that are embodied in the masks used to duplicate those designs on silicon. These regimes have been extended gradually and incrementally to cover creative activity not originally envisioned by legislators. The extensive use of patents and copyrights by the managers of high technology companies and the liberal granting of intellectual property rights to those firms has created a bit of a backlash and occasionally bitter fights.</p>

<p>One outcome of this contestation is the judicial delimitation of intellectual property rights in key decisions that are often lumped under the rubric of "fair use."  Section 107 of the Copyright Act of 1976 reads as follows: "... the fair use of a copyrighted work, including such use by reproduction in copies or phonorecords or by any other means specified by that section, for purposes such as criticism, comment, news reporting, teaching (including multiple copies for classroom use), scholarship, or research, is not an infringement of copyright."   This portion of the act has been used in a variety of court decisions not just to protect academics and journalists but also, increasingly, artists, disk jockeys, and others using "sampled" information to create something new.  </p>

<p>With the increasing digitalization of media content that has accompanied digitalization of the telephone networks, the rise of the Internet, the World Wide Web and now the transition to digital television, the ease of copying digitized texts, images, audio and visual materials has generated a whole new generation of digital piracy - that is, the theft of content protected by IPRs by illegal copying and sale of that content. This was possible prior to digitalization of course but digitalization has made the process faster, cheaper, and easier.  As the speed of computers and telecommunications networks continues to increase, so does the size of the problem of illegal copying and sales of protected content.</p>

<p>In recent years, Congressional attempts to tighten IPRs in the new digital environment took the form of the Digital Millennium Copyright Act (DMCA) of 1998 and the Inducing Infringement of Copyrights Act of 2004. The Broadcast Flag decision of the FCC in 2004 was consistent with the spirit of the Congressional acts. In the DMCA, the Congress weighed in heavily on behalf of IP rights holders by stressing the responsibility of businesses that provide access to telecommunications networks to guard against illegal activities including illegal copying and file sharing. The act did not adequately address fair use in this new context, however, nor did it pay adequate attention to its potential impact on legitimate research activities. The Supreme Court had ruled in the "Betamax" decision, Sony Corp. v. Universal City Studios, that the sale of video recorders could not be banned because some consumers misused the machine to make illegal recordings. In addition, the Betamax case established the right of consumers to make copies of copyrighted materials for their personal use within the household as long as they did not attempt to sell the copies. The Betamax ruling was consistent with the more general principle was that a manufacturer or service provider should not be made responsible for the illegal use of their products and services by final consumers. Such a transfer of responsibility would make the manufacturer or service provider in effect an arm of the police. </p>

<p>The Inducing Infringements of Copyrights Act of 2004 is aimed at identifying and punishing one who "intentionally aids, abets, induces, counsels, or procures ...  to induce infringement" of copyright laws, but in fact the main targets of this particular legislation are the peer-to-peer or file-sharing networks established via networking software like the original Napster and its descendants: Kazaa, Grokster, and Morpheus. The bill was sponsored chiefly by Senator Orrin Hatch (R-Utah) in reaction to a 2003 court ruling that the use of file-sharing software was legal. Its main business supporters were the Recording Industry Association of America and the Motion Picture Association of America. Its main opponents included Congressman Rick Boucher (D-Va.), the Consumers Union, the American Library Association, and the Electronic Frontier Foundation.</p>

<p>On November 4, 2003, the FCC released its so-called "Broadcast Flag" decision. The basic idea behind the Broadcast Flag was that all DTV content that was protected by IPR laws would contain a coded digital "flag" that could be detected by any piece of DTV equipment.  Once the flag was detected by the circuitry of the device, it would be impossible to make copies of the content or to pass digital versions of the content to other devices.</p>

<p>The effect of the Broadcast Flag, therefore, would be to prevent consumers from making backup copies of high definition tapes and DVDs or to record high definition movies delivered over the air, via cable, or via satellite. Thus, for consumers, the Broadcast Flag, like the DMCA and the Inducing Infringements of Copyrights Act was a step backward for both home recording rights and fair use.</p>

<p><strong>Localism in Broadcasting</strong><br />
A related matter was maintaining the role of local television news broadcasting in providing political information to voters.  The FCC was bound to consider this question along with its concerns about the efficient use of broadcasting spectrum.  </p>

<p>Contemporary research on voting indicated that a large percentage of voters, more than a majority, received most of their information about local elections from local TV broadcasts. Prior to the nearly universal access to TV broadcasts, however, most citizens obtained that information from the print media.  Given the dependence of voters on TV news, the existence of local TV news broadcasts became an important pillar of American democracy.  </p>

<p>There were questions, of course, about the quality of information obtained in this way, and about the long-term impact on the quality of U.S. democracy that resulted from dependence on television news because of its heavy emphasis on visual images and short "sound bites" rather than the lengthier and more deliberative coverage of previous eras.  There was also some interesting research on whether dependence on TV news coverage made for a more manipulable public and overdependence of candidates on the raising of campaign funds to pay for TV advertising.</p>

<p>Even if there were legitimate concerns about the quality of local political information conveyed via local broadcasting (and other media), clearly if that flow of information was interrupted in the transition to DTV, then there had to be an alternative channel for conveying that information if local politics was to continue to play an important role in the federal system.  </p>

<p>The FCC, under the leadership of Michael Powell, began to address this issue by holding a series of hearings around the country about "localism" in broadcasting. On July 1, 2004, the FCC issued a  "Notice of Inquiry" (NOI) on localism in broadcasting partly as a response to the heavy criticism of an earlier decision reversing decades of enforcing rules designed to prevent concentration of ownership of media outlets in local communities. Following the issuance of the NOI, various "stakeholders" submitted documents to the FCC on this question and testified at hearings stating their views. The process was still ongoing as of February 2005.<br />
Conclusions</p>

<p>The transition to digital television in the United States will be delayed if the issues discussed above are not resolved swiftly and in a fair and equitable manner. If poor or elderly consumers are not subsidized, they will forced to buy their own converters. If they choose not to do so, as is quite likely, they will lose access to an important source of timely information about local politics. Either way, there is a loss to democratic legitimacy. So what may appear at first glance to be technical or budget-driven decision is really a political decision about who gets access and at what cost to the political process. Similarly, overzealous protection of the intellectual property of content producers can undermine the rights of consumers to use televisions and computers for educational or creative/artistic purposes, thus impoverishing our culture. While digital or high definition television may not be all that important in the larger scheme of things, a number of more important issues lie just below the surface.</p>

<p><strong>About the Author</strong><br />
Jeffrey Hart is Professor of Political Science at Indiana University, Bloomington, where he has taught international politics and international political economy since 1981. His first teaching position was at Princeton University from 1973 to 1980. He was a professional staff member of the President's Commission for a National Agenda for the Eighties from 1980 to 1981. Hart worked at the Office of Technology Assessment of the U.S. Congress in 1985-86 and helped to write their report, International Competition in Services (1987). He was visiting scholar at the Berkeley Roundtable on the International Economy, 1987-89. His major publications include The New International Economic Order (1983), Interdependence in the Post Multilateral Era (1985), Rival Capitalists (1992), (edited with Aseem Prakash) Globalization and Governance (1999), Coping with Globalization (2000), and Responding to Globalization (2000), (with Joan Edelmann Spero) The Politics of International Economic Relations 6th edition (2002), Technology, Television and Competition (2004), and scholarly articles in World Politics, International Organization, the British Journal of Political Science, New Political Economy, and the Journal of Conflict Resolution.<br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 20, 2005  3:52 PM</b>
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
			<?=getComments(103)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 103)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/06/the-politics-of-the-transition-to-dtv-jeff-hart.php" type="text/javascript" charset="utf-8"></script>
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