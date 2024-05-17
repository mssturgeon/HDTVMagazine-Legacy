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
		AND e.entry_id = 611";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 611 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 611 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 611";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/articles/2007/06/directv-the-march-to-100-national-high-definition-channels.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 611";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download DirecTV - The March to 100 National High Definition Channels" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="DirecTV - The March to 100 National High Definition Channels" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="DirecTV - The March to 100 National High Definition Channels" />
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
	<title>HDTV Magazine - DirecTV - The March to 100 National High Definition Channels</title>
	<meta name="keywords" content="sunday ticket, nfl sunday, ticket nfl, comcast sportsnet, national high, channels, directv, nfl, channel, ticket, sunday, west, fsn, east, networks, national, those, announced, year, discovery, comcast, network, included, likely, new" />
	<meta name="description" content="Unless you've been under a rock, or subscribed to Cable, you are likely intimately aware of DirecTV's promise of having 100 national high definition channels in service by the end of 2007. Sounds good, doesn't it? &lt;B&gt;100 National channels of HD content!&lt;/B&gt; As you might imagine, this raises more than a few questions. Some of the more frequent ones I've heard are:
&lt;ul&gt;
&lt;li&gt;What channels will these be?&lt;/li&gt;
&lt;li&gt;Will &lt;I&gt;{insert favorite channel here}&lt;/I&gt; be among them?&lt;/li&gt;
&lt;li&gt;How will they carry 100 HD channels if there aren't 100 networks with HD content?&lt;/li&gt;
&lt;li&gt;How much will this cost me?&lt;/li&gt;
&lt;/ul&gt;" />
	<meta name="title" content="DirecTV - The March to 100 National High Definition Channels" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="DirecTV - The March to 100 National High Definition Channels" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/articles/2007/06/directv-the-march-to-100-national-high-definition-channels.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="Unless you've been under a rock, or subscribed to Cable, you are likely intimately aware of DirecTV's promise of having 100 national high definition channels in service by the end of 2007. Sounds good, doesn't it? &lt;B&gt;100 National channels of HD content!&lt;/B&gt; As you might imagine, this raises more than a few questions. Some of the more frequent ones I've heard are:
&lt;ul&gt;
&lt;li&gt;What channels will these be?&lt;/li&gt;
&lt;li&gt;Will &lt;I&gt;{insert favorite channel here}&lt;/I&gt; be among them?&lt;/li&gt;
&lt;li&gt;How will they carry 100 HD channels if there aren't 100 networks with HD content?&lt;/li&gt;
&lt;li&gt;How much will this cost me?&lt;/li&gt;
&lt;/ul&gt;" />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=611', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/articles/2007/06/directv-the-march-to-100-national-high-definition-channels.php">DirecTV - The March to 100 National High Definition Channels</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>June 11, 2007</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=3&category=Programming">Programming</a></b>
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
				<p>Unless you've been under a rock, or subscribed to Cable, you are likely intimately aware of DirecTV's promise of having 100 national high definition channels in service by the end of 2007. Sounds good, doesn't it? <B>100 National channels of HD content!</B> As you might imagine, this raises more than a few questions. Some of the more frequent ones I've heard are:
<ul>
<li>What channels will these be?</li>
<li>Will <I>{insert favorite channel here}</I> be among them?</li>
<li>How will they carry 100 HD channels if there aren't 100 networks with HD content?</li>
<li>How much will this cost me?</li>
</ul>
</p>

<p>This article will attempt to answer every one of these questions. I will list DirecTV's current offerings as well as a list of those networks with which they have agreements for carriage starting this fall. I will also list those networks which are currently carried by other providers which DirecTV may pick up as part of this 100. And you know what the best part is? According to DirecTV, there are <B>no plans to increase the price</B> of the HD package above the current price of $9.99! The only unknown at this point is the quality. Will it be on par with the current offerings? Will it be better? Let's hope it's one of those two.</p><br />

<B>The Promise</B>
<p>This is apparently a point of confusion for many. DirecTV has announced the planned <B>capacity</B> for 150 national high definition channels. Many have mistakenly taken this to mean that they will have 150 HD channels by years-end, which was not promised, and is not likely. At CES this year, DirecTV announced their plans for <B>carriage</B> of 100 national high definition channels. From the January 8, 2007 press release:
<blockquote>DIRECTV, the nation's leading satellite television service provider, is hailing 2007 as the "Year of HD" with the planned launch and carriage of 100 national high-definition (HD) channels. With this substantial HD muscle, DIRECTV will offer three-times more HD programming than any other multi-channel distributor, with the majority of these channels launching in Q3.</blockquote></p>
<p>That is the quote by which they will be measured in this article.</p>
<p>All of this additional HD content is being made possible by the launch of two new satellites: DirecTV 10 and 11. These new satellites support a new transmission protocol as well as the more efficient MPEG4/AVC codec, which will allow much better use of the available bandwidth. DirecTV 10 will be launched later this summer, and will be operation in Q3 to support all this new programming. DirecTV 11 will be launched in early 2008 to support further expansion of HD programming. When these two satellites are operational, DirecTV will be able to deliver more than 1,500 local HD and digital channels and 150 national HD channels.</p><br />

<B>The Fine Print</B>
<p>Since the original announcement, DirecTV has come forward on a few occasions to help us understand how they will be getting to 100 HD channels by the end of this year. They've also dropped hints as to which channels WILL NOT be included. These "fine print" items are as follows:
<ul>
<li>Since the local East and West Networks feeds are "national HD channels", those count. There are currently 8 of these from channel 80-89.</li>
<li>In an <a href="/cgi-bin/ntlinktrack.cgi?http://www.multichannel.com/article/CA6429787.html">article at Multichannel News</a>, DirecTV Group chief financial officer Michael Palkovic indicated that some of these 100 channels will be multiple feeds from sports packages such as "NFL Sunday Ticket", adding that only 70 or 80 of the 100 promised would be considered year-round. Last season, DirecTV ran the Sunday Ticket HD NFL games on 9 channels in the 700 range.</li>
<li>In <a href="/cgi-bin/ntlinktrack.cgi?http://www.multichannel.com/article/CA6429787.html">that same article</a>, Mr. Palkovic also indicated that the Voom channels would NOT be among the 100. Specifically, he said "there is nothing like that that people would consider not really quality channels." So those are not included in the last table below.</li>
<li>In their latest press release, DirecTV stated they plan to offer the HD feeds of regional sports networks on a nationwide basis this fall, which means they will likely be counting those as well. They are included below.</li>
<li>Also in their latest press release, it was indicated that they have commitments from a number of other networks to launch their HD simulcasts, and that they will be added to the lineup for launch by year-end and will be announced at a later date. That gives us some hints as to which networks may be coming next.</li>
</ul></p><br />

<B>Current Channels</B>
<p><table class="type1b"><tr><td class="type1b_header">#</td><td class="type1b_header">Network</td><td class="type1b_header">Channel</td><td class="type1b_header">Notes</td></tr>
<tr><td class="grid">1</td><td class="grid">HBO HD (East)</td><td class="grid">70</td><td class="grid"></td></tr>
<tr><td class="grid">2</td><td class="grid">Showtime HD (East)</td><td class="grid">71</td><td class="grid"></td></tr>
<tr><td class="grid">3</td><td class="grid">ESPN2 HD</td><td class="grid">72</td><td class="grid"></td></tr>
<tr><td class="grid">4</td><td class="grid">ESPN HD</td><td class="grid">73</td><td class="grid"></td></tr>
<tr><td class="grid">5</td><td class="grid">Universal HD</td><td class="grid">74</td><td class="grid"></td></tr>
<tr><td class="grid">6</td><td class="grid">TNT HD</td><td class="grid">75</td><td class="grid">OK, so this one may be a stretch (pun intended), but we're giving them the benefit of the doubt.</td></tr>
<tr><td class="grid">7</td><td class="grid">Discovery HD Theater</td><td class="grid">76</td><td class="grid"></td></tr>
<tr><td class="grid">8</td><td class="grid">HDNet Movies</td><td class="grid">78</td><td class="grid"></td></tr>
<tr><td class="grid">9</td><td class="grid">HDNet</td><td class="grid">79</td><td class="grid"></td></tr>

<tr><td class="grid">10</td><td class="grid">CBS (East)</td><td class="grid">80</td><td class="grid"></td></tr>
<tr><td class="grid">11</td><td class="grid">CBS (West)</td><td class="grid">81</td><td class="grid"></td></tr>
<tr><td class="grid">12</td><td class="grid">NBC (East)</td><td class="grid">82</td><td class="grid"></td></tr>
<tr><td class="grid">13</td><td class="grid">NBC (West)</td><td class="grid">83</td><td class="grid"></td></tr>
<tr><td class="grid">14</td><td class="grid">ABC (East)</td><td class="grid">86</td><td class="grid"></td></tr>
<tr><td class="grid">15</td><td class="grid">ABC (West)</td><td class="grid">87</td><td class="grid"></td></tr>
<tr><td class="grid">16</td><td class="grid">Fox (East)</td><td class="grid">88</td><td class="grid"></td></tr>
<tr><td class="grid">17</td><td class="grid">Fox (West)</td><td class="grid">89</td><td class="grid"></td></tr>
<tr><td class="grid">18</td><td class="grid">RSN HD</td><td class="grid">95</td><td class="grid"></td></tr>
<tr><td class="grid">19</td><td class="grid">RSN HD</td><td class="grid">96</td><td class="grid"></td></tr>

<tr><td class="grid">20</td><td class="grid" nowrap="nowrap">National Geographic HD</td><td class="grid">98</td><td class="grid">This one is only available at select "sneak peek" times, typically in the middle of the night (i.e., not yet 24/7)</td></tr>
<tr><td class="grid">21</td><td class="grid">CD USA</td><td class="grid">101</td><td class="grid"></td></tr>
<tr><td class="grid">22</td><td class="grid">YES HD</td><td class="grid">622</td><td class="grid"></td></tr>
<tr><td class="grid">23</td><td class="grid">NESN HD</td><td class="grid">623</td><td class="grid"></td></tr>
<tr><td class="grid">24</td><td class="grid">SNY HD</td><td class="grid">625</td><td class="grid"></td></tr>
<tr><td class="grid">25</td><td class="grid">CSN HD</td><td class="grid">629</td><td class="grid"></td></tr>
<tr><td class="grid">26</td><td class="grid">NFL Sunday Ticket 1</td><td class="grid">719</td><td class="grid"></td></tr>
<tr><td class="grid">27</td><td class="grid">NFL Sunday Ticket 2</td><td class="grid">720</td><td class="grid"></td></tr>
<tr><td class="grid">28</td><td class="grid">NFL Sunday Ticket 3</td><td class="grid">721</td><td class="grid"></td></tr>
<tr><td class="grid">29</td><td class="grid">NFL Sunday Ticket 4</td><td class="grid">722</td><td class="grid"></td></tr>

<tr><td class="grid">30</td><td class="grid">NFL Sunday Ticket 5</td><td class="grid">723</td><td class="grid"></td></tr>
<tr><td class="grid">31</td><td class="grid">NFL Sunday Ticket 6</td><td class="grid">724</td><td class="grid"></td></tr>
<tr><td class="grid">32</td><td class="grid">NFL Sunday Ticket 7</td><td class="grid">725</td><td class="grid"></td></tr>
<tr><td class="grid">33</td><td class="grid">NFL Sunday Ticket 8</td><td class="grid">726</td><td class="grid"></td></tr>
<tr><td class="grid">34</td><td class="grid">NFL Sunday Ticket 9</td><td class="grid">727</td><td class="grid"></td></tr>
</table></p><br />

<B>Announced Channels</B>
<p>The following channels have been announced for availability this fall:</p>
<p><table class="type1b"><tr><td class="type1b_header">#</td><td class="type1b_header">Network</td><td class="type1b_header">Announced</td><td class="type1b_header">Notes</td></tr>
<tr><td class="grid">35</td><td class="grid">A&E HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">36</td><td class="grid">ABC Family HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">37</td><td class="grid">Animal Planet HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">38</td><td class="grid">Bravo HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">39</td><td class="grid">Big Ten Network</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>

<tr><td class="grid">40</td><td class="grid">Cartoon Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">41</td><td class="grid">Chiller HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">42</td><td class="grid">CNBC HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">43</td><td class="grid">CNN HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">44</td><td class="grid">Discovery Channel HD</td><td class="grid">5/23/2007</td><td class="grid">This is different from Discovery HD Theater, which DirecTV already carries. Discovery Channel HD will be an HD simulcast of the "regular" Discovery Channel</td></tr>
<tr><td class="grid">45</td><td class="grid">TBD</td><td class="grid">5/23/2007</td><td class="grid" rowspan="2">Discovery will be announcing two networks to be launched at a later date and carried by DirecTV</td></tr>
<tr><td class="grid">46</td><td class="grid">TBD</td><td class="grid">5/23/2007</td></tr>
<tr><td class="grid">47</td><td class="grid">Disney Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">48</td><td class="grid">ESPNews HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">49</td><td class="grid">Food Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>

<tr><td class="grid">50</td><td class="grid">FX HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">51</td><td class="grid">HGTV HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">52</td><td class="grid">The Movie Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">53</td><td class="grid">MTV HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">54</td><td class="grid">NFL Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">55</td><td class="grid">SciFi Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">56</td><td class="grid">Science Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">57</td><td class="grid">Speed Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">58</td><td class="grid">TBS HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">59</td><td class="grid">Toon Disney HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>

<tr><td class="grid">60</td><td class="grid">History Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">61</td><td class="grid">Showtime HD (West)</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">62</td><td class="grid">Starz HD (East)</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">63</td><td class="grid">Starz HD (West)</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">64</td><td class="grid">Starz Edge HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">65</td><td class="grid">Starz Comedy HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">66</td><td class="grid">Starz Kids &amp; Family HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">67</td><td class="grid">Tennis Channel HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">68</td><td class="grid" nowrap="nowrap">TLC (The Learning Channel) HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>
<tr><td class="grid">69</td><td class="grid">Versus HD</td><td class="grid">5/23/2007</td><td class="grid"></td></tr>

<tr><td class="grid">70</td><td class="grid">Weather Channel HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
<tr><td class="grid">71</td><td class="grid">USA Network HD</td><td class="grid">1/8/2007</td><td class="grid"></td></tr>
</table></p><br />

<B>Other Channels</B>
<p>So what will round out the 100 channels? As mentioned in "fine print" above, the nationally broadcast special sports packages will count, so we have included some of those in the table below. We have also included some other channels here that are currently carried by other networks which may end up being announced as part of DirecTV's initiative. Note that the channels listed below HAVE NOT necessarily been announced by DirecTV.</p>

<p><table class="type1b"><tr><td class="type1b_header">#</td><td class="type1b_header">Network</td><td class="type1b_header">Notes</td></tr>
<tr><td class="grid">72</td><td class="grid">NFL Sunday Ticket 10</td><td class="grid" rowspan="4">Since DirecTV carries as many as 13 games any given week, we'll assume that they will broadcast 100% HD this year and add an additional 4 channels to what they carried last year.</td></tr>
<tr><td class="grid">73</td><td class="grid">NFL Sunday Ticket 11</td></tr>
<tr><td class="grid">74</td><td class="grid">NFL Sunday Ticket 12</td></tr>
<tr><td class="grid">75</td><td class="grid">NFL Sunday Ticket 13</td></tr>
<tr><td class="grid">76</td><td class="grid">PBS (East)</td><td class="grid" rowspan="2">These are included only because they are the only "major" network missing from their current East/West feeds. Just a guess.</td></tr>
<tr><td class="grid">77</td><td class="grid">PBS (West)</td></tr>
<tr><td class="grid">78</td><td class="grid">Cinemax HD (East)</td><td class="grid"></td></tr>
<tr><td class="grid">79</td><td class="grid">Cinemax HD (West)</td><td class="grid"></td></tr>

<tr><td class="grid">80</td><td class="grid">Nickelodeon HD</td><td class="grid"></td></tr>
<tr><td class="grid">81</td><td class="grid">Outdoor Channel 2 HD</td><td class="grid"></td></tr>
<tr><td class="grid">82</td><td class="grid">Playboy HD</td><td class="grid"></td></tr>
<tr><td class="grid">83</td><td class="grid">Wealth TV HD</td><td class="grid"></td></tr>

<tr><td class="grid">84</td><td class="grid">FSN Arizona</td><td class="grid" rowspan="12">Granted, not all of these are likely to be added, but these are the networks <a href="/cgi-bin/ntlinktrack.cgi?http://msn.foxsports.com/story/1528357">as indicated by Fox Sports</a> that are slated to produce local HD programs.</td></tr>
<tr><td class="grid">85</td><td class="grid">FSN Bay Area</td></tr>
<tr><td class="grid">86</td><td class="grid">FSN Florida</td></tr>
<tr><td class="grid">87</td><td class="grid">FSN North</td></tr>
<tr><td class="grid">88</td><td class="grid">FSN Northwest</td></tr>
<tr><td class="grid">89</td><td class="grid">FSN BPittsburgh</td></tr>

<tr><td class="grid">90</td><td class="grid">FSN South</td></tr>
<tr><td class="grid">91</td><td class="grid">FSN Southwest</td></tr>
<tr><td class="grid">92</td><td class="grid">Sun Sports</td></tr>
<tr><td class="grid">93</td><td class="grid">FSN Utah</td></tr>
<tr><td class="grid">94</td><td class="grid">FSN West</td></tr>
<tr><td class="grid">95</td><td class="grid">FSN West 2</td></tr>
<tr><td class="grid">96</td><td class="grid">Comcast SportsNet - Philadelphia</td><td class="grid" rowspan="5">Again, not all of these are likely to be added, but these are the networks on which Comcast currently carries HD programming</td></tr>
<tr><td class="grid">97</td><td class="grid" nowrap="nowrap">Comcast SportsNet - Baltimore/Washington D.C.</td></tr>
<tr><td class="grid">98</td><td class="grid">Comcast SportsNet - Chicago</td></tr>
<tr><td class="grid">99</td><td class="grid">Comcast SportsNet - Sacramento</td></tr>
<tr><td class="grid">100</td><td class="grid">Comcast SportsNet - New York</td></tr>
</table></p><br />

<b>In Summary</b>
<p>There you have it, 100 exactly. I could go on with other options, but I think I've hit the most likely candidates. Regardless of who your HD provider is this is good news. If you're already with DirecTV, great. If not, your provider will certainly be wondering how they can keep up. For updates to the above tables, please check the page we have dedicated to <a href="http://www.hdtvmagazine.com/programming/satellite/directv.php">DirecTV's HD offerings</a>.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>June 11, 2007  6:45 AM</b>
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
			<?=getComments(611)?>
			<div class="dottedline"></div>

			<? if (1 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 611)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/06/directv-the-march-to-100-national-high-definition-channels.php" type="text/javascript" charset="utf-8"></script>
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