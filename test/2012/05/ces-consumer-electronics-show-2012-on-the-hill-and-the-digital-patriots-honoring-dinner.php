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

	# Get author information
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4793 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4793
		AND a.topic_id = t.topic_id";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/05/ces-consumer-electronics-show-2012-on-the-hill-and-the-digital-patriots-honoring-dinner.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (6) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4793";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner" />
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
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

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
	<title>HDTV Magazine - CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner</title>
	<meta name="keywords" content="photo cea, consumer electronics, mobile video, open mobile, digital patriots, cea, mobile, electronics, photo, shown, video, consumer, open, patriots, ces, digital, dinner, demoing, ceo, washington, director, president, david, hill, technology" />
	<meta name="description" content="As I did on previous years since 2004 when it was known as “CEA’s HDTV Summit: Partnership, Policy and Profits and 2003 Academy of Digital Television Pioneers Awards”, this Tuesday April 24th I attended again the “CES on the Hill” press event, this time in the Rayburn House Office Building, Washington, DC, and the following day I was also invited to the Digital Patriots dinner, this time at the Newseum in Washington, DC, to honor three leaders that contributed their efforts to the consumer electronics industry, those are Senator Ron Wyden and Representative Jason Chaffetz, and I quote “for their support of technology innovation”, and David Rubenstein, Managing Director of Carlyle Group, “for his efforts to advance technology”, as said by the Consumer Electronics Association’s President and CEO, Gary Shapiro.

The Consumers Electronics Association, as profiled, is..." />
	<meta name="title" content="CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/05/ces-consumer-electronics-show-2012-on-the-hill-and-the-digital-patriots-honoring-dinner.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="As I did on previous years since 2004 when it was known as “CEA’s HDTV Summit: Partnership, Policy and Profits and 2003 Academy of Digital Television Pioneers Awards”, this Tuesday April 24th I attended again the “CES on the Hill” press event, this time in the Rayburn House Office Building, Washington, DC, and the following day I was also invited to the Digital Patriots dinner, this time at the Newseum in Washington, DC, to honor three leaders that contributed their efforts to the consumer electronics industry, those are Senator Ron Wyden and Representative Jason Chaffetz, and I quote “for their support of technology innovation”, and David Rubenstein, Managing Director of Carlyle Group, “for his efforts to advance technology”, as said by the Consumer Electronics Association’s President and CEO, Gary Shapiro.

The Consumers Electronics Association, as profiled, is..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Testing Grounds Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4793', 340, 125);">Link Products</a>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/05/ces-consumer-electronics-show-2012-on-the-hill-and-the-digital-patriots-honoring-dinner.php">CES (Consumer Electronics Show) 2012 on the Hill, And the Digital Patriots Honoring Dinner</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>May  1, 2012</b>
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
			<div id="<?=$container?>">
				<?=getReviewHeader($row_aux['ASIN'], $amazon_tracking_id, $pg_url, $pg_price)?>
				<!--?=$v_buttons?-->
				<p><span class="caption left"><img alt="Newseum Washington DC (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image004_523b9c4c-594e-467c-b8bd-32e8d5a1576a.jpg" width="310" height="208"><br />Newseum Washington DC (photo CEA)</span><span class="caption right" style="width:170px"><a href="http://www.ce.org/About-CEA/Executive-Profile/CEA-Executive-Board/Gary-Shapiro.aspx"><img alt="Gary Shapiro, President and CEO Consumer Electronics Association (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image010_d3512c24-5e7f-4b82-ab8c-530246f5ba23.jpg" width="168" height="210"></a><br />Gary Shapiro, President and CEO Consumer Electronics Association (photo CEA)</span>As I did on <a href="http://www.hdtvmagazine.com/articles/2011/05/ces-consumer-electronics-show-2011-at-the-hill-and-the-digital-patriots-event-is-government-actually-listening-to-what-innovation-can-do.php">previous</a> <a href="http://www.hdtvmagazine.com/articles/2011/05/ces-consumer-electronics-show-2011-at-the-hill-and-the-digital-patriots-event-who-can-actually-use-more-innovation.php">years</a> since 2004 when it was known as “CEA’s HDTV Summit: Partnership, Policy and Profits and 2003 Academy of Digital Television Pioneers Awards”, this Tuesday April 24th I attended again the “<a href="http://www.youtube.com/watch?v=_x0miRd2_8I&amp;feature=youtube_gdata">CES on the Hill</a>” press event, this time in the Rayburn House Office Building, Washington, DC, and the following day I was also invited to the <a href="http://blog.ce.org/index.php/2012/03/15/nations-capital-welcomes-ces-on-the-hill-and-ceas-digital-patriots-dinner/">Digital Patriots dinner</a>, this time at the <a href="http://www.newseum.org/">Newseum</a> in Washington, DC, to honor three leaders that contributed their efforts to the consumer electronics industry, those are Senator Ron Wyden and Representative Jason Chaffetz, and I quote “for their support of technology innovation”, and David Rubenstein, Managing Director of Carlyle Group, “for his efforts to advance technology”, as said by the <a href="http://www.ce.org/">Consumer Electronics Association</a>’s President and CEO, <a href="http://www.ce.org/About-CEA/Executive-Profile/CEA-Executive-Board/Gary-Shapiro.aspx">Gary Shapiro</a>. <br clear="all" /><span class="caption left" style="width:248px"><img alt="David Rubenstein, Managing Director of Carlyle Group, accepting honor from Gary Shapiro, President and CEO of CEA (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image006_118ca3ac-bc52-4d93-9b03-d32cd6e5d5f5.jpg" width="246" height="366"><br />David Rubenstein, Managing Director of Carlyle Group, accepting honor from Gary Shapiro, President and CEO of CEA (photo CEA)</span><span class="caption right" style="width:226px"><img alt="David Rubenstein, Managing Director of Carlyle Group, acceptance speech (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image008_05386fe3-b9ff-43fe-b205-610ded8cbc23.jpg" width="224" height="336"><br />David Rubenstein, Managing Director of Carlyle Group, acceptance speech (photo CEA)</span><p>The Consumers Electronics Association, as profiled, is “the U.S. trade association representing more than 2,000 consumer electronics companies and owning and producing the continent's largest annual tradeshow, the <a href="http://www.cesweb.org/">International CES</a>,... [which] unites more than 100,000 retail buyers, distributors, manufacturers, market analysts, importers, exporters, and press from 140 countries.” <br clear="all" /><span class="caption right"><img alt="Digital Patriots Dinner (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image016_a59302a7-2430-4a77-be9e-cb687e4aa8e4.jpg" width="240" height="359"><br />Digital Patriots Dinner (photo CEA)</span><span class="caption left"><img alt="CES on the Hill (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image022_254f3661-8aae-4c06-9210-1a6ba70c1422.jpg" width="279" height="187"><br />CES on the Hill (photo CEA)</span><span class="caption left" style="width:268px"><img alt="John Taylor of LG Electronics USA demoing the 3DTV (photo CEA)" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image018_020b5ba6-73a0-4d85-bc2d-6349ebdae314.jpg" width="266" height="179"><br />John Taylor of LG Electronics USA demoing the 3DTV (photo CEA)</span><p>Relative to the annual International <a href="http://link.brightcove.com/services/player/bcpid59348424001?bckey=AQ~~,AAAABh3C_dE~,zBkXqCU8KVZ251m5EVgAOpsRSvnZ05Pp&amp;bclid=1377100474001&amp;bctid=1389622213001">2012 CES</a> with 3100 exhibitors, 153,000 attendees, 1.86 million of sq. feet of exhibit space, “CES on the Hill” showed a very small number of product innovations in an environment suited for networking with leaders, policy makers, and their staff, who (luckily avoiding the Washington rush hour) just have to cross the street from Capitol Hill to see the innovations. <p><br clear="all" /><span class="caption right"><img alt="Open Mobile Video Coalition demos" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image014_c8e5ee7f-e7cd-4230-843b-830dde0228fb.jpg" width="242" height="185"><br />Open Mobile Video Coalition demos</span><span class="caption left"><img alt="Syncback's Jack Perry and Staff" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image018_8d7303d0-4d66-411b-86ab-99792c363f64.jpg" width="290" height="194"><br />Syncback's Jack Perry and Staff</span>Some of the products shown at CES on the Hill were: the <a href="http://www.hdtvmagazine.com/podcast/2012/04/hdtv-and-home-theater-podcast-podcast-527-the-hopper-by-dish.php">Hopper whole house DVR system</a> from DISH Network shown by Mr. Aaron M. Johnson, Corporate Communications Manager. LG’s passive 3D LCD TV shown by John Taylor, LG Electronics USA’s Vice President of Government Relations and Communications (long time friend of HDTV Magazine’s co-owner Dale Cripps, and mine as well). <br clear="all" /><p><span class="caption left"><img alt="Open Mobile Video Coalition" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image021_a2c3efe6-33ea-42a6-98b1-dc0197ce6e34.jpg" width="259" height="189"><br />Open Mobile Video Coalition</span><span class="caption right"><img alt="Open Mobile Video Coalition" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image024_6b9eefc0-1b46-44b1-bf86-1d92efc7d217.jpg" width="265" height="178"><br />Open Mobile Video Coalition</span>Sony’s new tablet was shown by Jim Morgan, Director and Counsel, Government and Industry Affairs. Syncbak, an Internet Broadcast Platform for live mobile devices and OTT that uses cellular and Wi-Fi rather than the over-the-air mobile broadcasting technology, was shown by Jack Perry, Founder and CEO Syncbak, Inc. (remember Antenna Web?). <br clear="all" /><p><span class="caption left"><img alt="Open Mobile Video Coalition demo" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image027_ccad54e7-3233-4f53-ac7a-b1a4cda155d3.jpg" width="251" height="193"><br />Open Mobile Video Coalition demo</span><span class="caption right"><img alt="Qualcomm demoing H.265" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image030_8b4b27a1-e8b6-4160-92ca-146059b43f33.jpg" width="265" height="180"><br />Qualcomm demoing H.265</span>Just across the hall, the Open Mobile Video Coalition was demoing their products and system for a similar purpose of mobile TV, but rather using over-the-air broadcasting bandwidth, which now claims to use only a few Kbps from the 19 Mbps a HDTV signal ideally would need for quality from the allotted 6 MHz channel bandwidth (the image was very clear on the mall devices demo). A demo of the more efficient H.265 compression standard soon to be implemented was compared with the currently used H.264 MPEG-4; H.265 may open the doors for a near future 4K Blu-ray using the same disc (so I can have content for my 4K projector). <p><span class="caption left"><img alt="Qualcomm demoing H.265 compared to H.264" src="http://www.hdtvmagazine.us/articles/images/ecca1731aff1_13997/clip_image033_a9e80073-f98a-47bd-bd87-e174d3d443b8.jpg" width="345" height="151"><br />Qualcomm demoing H.265 compared to H.264</span>I had the pleasure and opportunity to share the dining table with Tom Butts, Editor-in-Chief of TV Technology magazine, NewBay Media L.L.C., with whom I agree on many of his views, especially about the struggle that over-the-air broadcasters are going thru with the FCC and the nation’s plans to redistribute their bandwidth for other purposes. <p>And finally, do you know which speech of the Digital Patriots Dinner I liked the best? Mr. David Rubenstein’s by far. His words, out of the typical political rhetoric of Washington’s Congress, were very down the earth and were said with humility, giving sincere recognition to who in his view were the real patriots in this country. Mr. Rubinstein was for me the best of the event, congratulations. 
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>May  1, 2012  7:34 PM</b>
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
			<?=getComments(4793)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4793)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About Shane Sturgeon</h2>
					<?=stripslashes($author['bio_short'])?>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/05/ces-consumer-electronics-show-2012-on-the-hill-and-the-digital-patriots-honoring-dinner.php" type="text/javascript" charset="utf-8"></script>
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