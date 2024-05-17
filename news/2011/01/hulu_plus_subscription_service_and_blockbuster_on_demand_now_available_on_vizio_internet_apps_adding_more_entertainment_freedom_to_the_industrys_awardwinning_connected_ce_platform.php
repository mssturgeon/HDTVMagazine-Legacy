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
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short, aux_e.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM mt_entry e, mt_author a, aux_author aux_a, aux_mt_entry aux_e
	LEFT JOIN phpbb3_topics t ON (aux_e.topic_id = t.topic_id)
	WHERE e.entry_id = aux_e.entry_id
		AND a.author_id = aux_a.author_id
		AND e.entry_author_id = a.author_id
		AND e.entry_id = 4124";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$author_bio = $author['bio_short'];
	$amazon_tracking_id = ($row_aux['amazon_tracking_id'] != '') ? $row_aux['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $row_aux['channel'];
	$viglink_source = $row_aux['viglink_source'];

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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4124 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4124 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4124";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/news/2011/01/hulu-plus-subscription-service-and-blockbuster-on-demand-now-available-on-vizio-internet-apps-adding-more-entertainment-freedom-to-the-industrys-awardwinning-connected-ce-platform.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (7) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4124";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Hulu Plus&trade; Subscription Service and BLOCKBUSTER On Demand&reg; Now Available on VIZIO Internet Apps&trade; Adding More Entertainment Freedom to the Industry\'s Award-Winning Connected CE Platform" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Hulu Plus&trade; Subscription Service and BLOCKBUSTER On Demand&reg; Now Available on VIZIO Internet Apps&trade; Adding More Entertainment Freedom to the Industry\'s Award-Winning Connected CE Platform" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Hulu Plus&trade; Subscription Service and BLOCKBUSTER On Demand&reg; Now Available on VIZIO Internet Apps&trade; Adding More Entertainment Freedom to the Industry\'s Award-Winning Connected CE Platform" />
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
	<title>HDTV Magazine - Hulu Plus&trade; Subscription Service and BLOCKBUSTER On Demand&reg; Now Available on VIZIO Internet Apps&trade; Adding More Entertainment Freedom to the Industry's Award-Winning Connected CE Platform</title>
	<meta name="keywords" content="internet apps, apps via, blu ray, hulu plus, blockbuster demand, apps, internet, via, demand, platform, available, plus, blu, ray, blockbuster, skype, hulu, new, experience, movies, players, including, award, america, top" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-hulu-blockbuster.jpg&quot; alt=&quot;Vizio Hulu Blockbuster&quot; height=&quot;72&quot; width=&quot;67&quot; style=&quot;float:left; padding:0 5px 5px 0yimg&quot;&gt;VIZIO, America's #1 LCD HDTV Company*, announced today a significant expansion of the VIZIO Internet Apps (VIA) platform with the addition of Hulu Plus&amp;trade; subscription service and BLOCKBUSTER On Demand&amp;reg;. These new apps further strengthen the diverse catalog of entertainment choices available through VIZIO Internet Apps (VIA) Connected CE platform on 2010's best-ranked LCD HDTVs and select Blu-ray players. Several other partners soon to launch their content and services to the award-winning VIZIO Internet Apps (VIA) platform include..." />
	<meta name="title" content="Hulu Plus&amp;trade; Subscription Service and BLOCKBUSTER On Demand&amp;reg; Now Available on VIZIO Internet Apps&amp;trade; Adding More Entertainment Freedom to the Industry's Award-Winning Connected CE Platform" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Hulu Plus&amp;trade; Subscription Service and BLOCKBUSTER On Demand&amp;reg; Now Available on VIZIO Internet Apps&amp;trade; Adding More Entertainment Freedom to the Industry's Award-Winning Connected CE Platform" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/news/2011/01/hulu-plus-subscription-service-and-blockbuster-on-demand-now-available-on-vizio-internet-apps-adding-more-entertainment-freedom-to-the-industrys-awardwinning-connected-ce-platform.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="&lt;img src=&quot;http://www.hdtvmagazine.us/news/images/vizio-hulu-blockbuster.jpg&quot; alt=&quot;Vizio Hulu Blockbuster&quot; height=&quot;72&quot; width=&quot;67&quot; style=&quot;float:left; padding:0 5px 5px 0yimg&quot;&gt;VIZIO, America's #1 LCD HDTV Company*, announced today a significant expansion of the VIZIO Internet Apps (VIA) platform with the addition of Hulu Plus&amp;trade; subscription service and BLOCKBUSTER On Demand&amp;reg;. These new apps further strengthen the diverse catalog of entertainment choices available through VIZIO Internet Apps (VIA) Connected CE platform on 2010's best-ranked LCD HDTVs and select Blu-ray players. Several other partners soon to launch their content and services to the award-winning VIZIO Internet Apps (VIA) platform include..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Bulletins Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4124', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/news/2011/01/hulu-plus-subscription-service-and-blockbuster-on-demand-now-available-on-vizio-internet-apps-adding-more-entertainment-freedom-to-the-industrys-awardwinning-connected-ce-platform.php">Hulu Plus&trade; Subscription Service and BLOCKBUSTER On Demand&reg; Now Available on VIZIO Internet Apps&trade; Adding More Entertainment Freedom to the Industry's Award-Winning Connected CE Platform</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>January  5, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=323&category=Internet HD Video">Internet HD Video</a></b>
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
				<p class="prtitle">Hulu Plus&trade; Subscription Service and BLOCKBUSTER On Demand&reg; Now Available on VIZIO Internet Apps&trade; Adding More Entertainment Freedom to the Industry's Award-Winning Connected CE Platform</p>

<center><i>- VIZIO's industry-leading Connected CE platform, VIZIO Internet Apps (VIA), continues its innovation and leadership position with the addition of Hulu Plus and BLOCKBUSTER On Demand&reg;<br /><br />- Hulu Plus offers instant access to the entire current season of hit TV shows like Glee, The Office, and Modern Family as well as classic favorites like Battlestar Galactica, and The X-Files<br /><br />- BLOCKBUSTER On Demand&reg; delivers new release movies on the same day as they become available on DVD and Blu-ray<br /><br />- Additional partners available soon on the platform include BigStar, MOG, NAMCO BANDAI Games America Inc., OnLive, Skype, and Vimeo.</center></i><br />
<br />

<p><img src="http://www.hdtvmagazine.us/news/images/vizio-hulu-blockbuster.jpg" alt="Vizio Hulu Blockbuster" height="144" width="134" class="keyimg"><strong>IRVINE, Calif., Jan. 5, 2011 /PRNewswire/ -- </strong>VIZIO, America's #1 LCD HDTV Company*, announced today a significant expansion of the VIZIO Internet Apps (VIA) platform with the addition of Hulu Plus&trade; subscription service and BLOCKBUSTER On Demand&reg;. These new apps further strengthen the diverse catalog of entertainment choices available through VIZIO Internet Apps (VIA) Connected CE platform on 2010's best-ranked LCD HDTVs and select Blu-ray players. Several other partners soon to launch their content and services to the award-winning VIZIO Internet Apps (VIA) platform include BigStar, MOG, NAMCO BANDAI Games America Inc., OnLive, Skype, and Vimeo.</p>

<p>"Our VIZIO Internet Apps platform continues to be the most innovative and compelling connected experience for consumer electronics," said Matthew McRae, Chief Technology Officer at VIZIO. "Service and application partners focus on VIZIO because of the seamless user experience, class leading features, and award winning devices.  The result is a product that gives consumers unprecedented choice and access to the best of what the web has to offer."</p>

<p>Hulu Plus enables subscribers to stream instantly the deepest available library of current hit TV shows from ABC, FOX and NBC for only $7.99 per month. Hulu Plus offers the full seasons of many current hit television shows like Glee, Family Guy, Grey's Anatomy, Modern Family, 30 Rock, House and The Office. Viewers will also be able to enjoy classic shows from an extensive back catalog, including The X-Files, Arrested Development, Battlestar Galactica, Monk, Saturday Night Live, Law &amp; Order: Special Victims Unit, Ugly Betty, Buffy the Vampire Slayer, and many more.</p>

<p>BLOCKBUSTER On Demand delivers a wide range of movies, including new releases as soon as they become available on DVD and Blu-ray. Rental prices range from $2.99 to $3.99, and purchase prices range from $5.99 to $19.99. BLOCKBUSTER On Demand enables customers to rent or buy not only the latest hit movies, such as Inception, Shrek Forever After and The Twilight Saga: Eclipse , but also classics like Caddyshack, Superman and Back to the Future.</p>

<p><br />
<strong>VIZIO Internet Apps Continues to Grow</strong></p>

<p>The VIZIO Internet Apps (VIA) Connected CE platform delivers unprecedented choice and control of web-based content and services directly to the television without the need of a PC or set-top box. The platform spans across VIZIO's E, M and XVT Series HDTVs, from sizes as small as 22" class, up to 65" class, with select apps also available on certain VIZIO Blu-ray Players.  VIZIO Internet Apps (VIA) features top online content and service brands including: Amazon Video On Demand, Facebook&reg;, Flickr&reg;, Netflix, Rhapsody&reg;, Pandora&reg;, Twitter&trade;, VUDU&reg;, and Yahoo!&reg; TV Widgets, as well as several new apps recently released, including: Fandango&reg;, iMemories&reg;, MediaBox&trade;, My-Cast&reg;, TuneIn Radio&trade;, Web Videos, Wiki TV and Yahoo Fantasy Football .</p>

<p>Several new partners have also signed on to deliver their services through the VIZIO Internet Apps (VIA) platform including BigStar, MOG, NAMCO BANDAI Games America Inc., OnLive, Skype, and Vimeo.</p>

<p>BigStar (www.bigstar.tv) – BIGSTAR, the social discovery and demand platform, gives access to thousands of unique movies and entertainment. Watch Indies, foreign films, festival award winners and more!</p>

<p>MOG (<a target="_blank" href="http://www.mog.com/">www.mog.com</a>) – MOG is the award-winning, on demand digital music service that provides unlimited access to over 10 million songs from over a million albums in CD quality through a wide range of devices including mobile phones, tablets, TVs, Blu-ray players and the Web.</p>

<p>NAMCO BANDAI Games America Inc. (<a target="_blank" href="http://www.namcobandaigames.com/">www.namcobandaigames.com</a>) - Namco Bandai provides top-notch interactive gaming to multiple platforms. With the expansion to the VIZIO Internet Apps (VIA) platform, Namco's rich franchises will be available to an even broader audience.</p>

<p>OnLive (<a target="_blank" href="http://www.onlive.com/">www.onlive.com</a>), the pioneer of  instant-play cloud gaming, delivers premium video games on demand to a range of electronic devices, empowering gamers to anytime, anywhere, instantly.</p>

<p>Skype (<a target="_blank" href="http://www.skype.com/">www.skype.com</a>) – The Skype App in VIZIO Internet Apps (VIA) HDTVs will work in conjunction with the VIZIO Video Camera VCP100 that attaches to any VIZIO Internet Apps (VIA) HDTV through a USB connection. When used together, users can enjoy HD quality 720p video chat at up to 30 frames per second over their high-speed Internet connection. Perfect for staying in touch with friends and loved ones, the Skype App also lets you chat over instant messenger with other Skype users.</p>

<p>Vimeo (<a target="_blank" href="http://www.vimeo.com) – Vimeo's newest app allows people to watch their original videos in high quality on Vimeo.com/">www.vimeo.com) – Vimeo's newest app allows people to watch their original videos in high quality on Vimeo.com</a> along with thousands of other inspiring works from talented creators.</p>

<p>Additionally, 3D fans can look forward to instant streaming of 3D movies from VUDU. At CES this year, VUDU began offering 3D content for enabled TVs and Blu-ray Players, which will soon include VIZIO HDTVs and Blu-ray Players. With the addition of 3D streaming, VUDU enriches its industry leading cinematic streaming experience, an experience that already includes instant HD and HDX 1080p and Dolby Digital Plus 5.1 Surround Sound. Combined with VIZIO's new Theater 3D&trade; HDTVs which utilize lightweight and battery-free eyewear and provide up to 2X brighter and flicker-free picture quality, users will be able to enjoy the ultimate 3D experience without leaving their living rooms.</p>

<p>In further support of the cinematic experience, VIZIO will be demonstrating a new app at their private CES showroom at the Wynn Hotel. The new VIZIO On Demand app will enable users to access movies in CinemaScope aspect ratio for a pure movie experience on VIZIO's Cinema HDTV&trade; displays, 4K by 2K content for Ultra HD TVs that can display up to four times the resolution of 1080p HD, 3D movies, and a deep catalog of top movie favorites, all available to stream direct to VIZIO Internet Apps (VIA)-enabled TVs.</p>

<p>Wireless connectivity is central to the VIZIO Internet Apps (VIA) experience with 802.11n dual-band Wi-Fi conveniently built in for high performance.  With VIZIO Internet Apps (VIA), viewers can enjoy the convenience of on demand movies, TV shows, social networking, music, photos and more with just the push of a button on the premium Bluetooth QWERTY keyboard universal remote with slide out keypad (XVT Models only).</p>

<p>VIZIO Internet Apps (VIA) Blu-ray Players also deliver select apps with most models sporting built-in 802.11n Wi-Fi connection as well.</p>

<p>Hulu Plus and BLOCKBUSTER On Demand will be rolling out this week to VIZIO Internet Apps (VIA) HDTVs through automatic updates over their Internet connection. Soon to follow, VIZIO 3D Blu-ray Players with Wireless Apps (VBR333 and VBR334) will receive updates over the Internet that will add these apps as well.</p>

<p>*Sources: Q3 2010 iSuppli and DisplaySearch Reports</p>

<p><br />
<strong>About VIZIO</strong></p>

<p>VIZIO, Inc., "Entertainment Freedom For All," headquartered in Irvine, California, is America's HDTV and Consumer Electronics Company. In 2007, VIZIO skyrocketed to the top by becoming the #1 selling brand of flat panel HDTVs in North America and became the first American brand in over a decade to lead in U.S. TV sales. Since 2007 VIZIO HDTV shipments remain in the TOP ranks in the U.S. and was #1 for the total year in 2009.  VIZIO is committed to bringing feature-rich consumer electronics to market at a value through practical innovation. VIZIO offers a broad range of award winning consumer electronics. VIZIO's products are found at Costco Wholesale, Sam's Club, Walmart, Target, BJ's Wholesale, and other retailers nationwide along with authorized online partners. VIZIO has won numerous awards including a #1 ranking in the Inc. 500 for Top Companies in Computers and Electronics, Fast Company's 6th Most Innovative CE Company of 2009, and made the lists of Ad Age's Hottest Brands, Good Housekeeping's Best Big-Screens, CNET's Editor's Choice, PC World's Best Buy and OC Metro's 10 Most Trustworthy Brands among many other prestigious honors. For more information, please call 888-VIZIOCE or visit on the web at <a target="_blank" href="http://www.VIZIO.com/">www.VIZIO.com</a>.</p>

<p>The V, VIZIO, TruLED, Extreme VIZIO Technology XVT, VIZIO Internet Apps, VIA Plus, 480Hz SPS, 240Hz SPS, Thin Line, Smooth Motion, Razor LED, Smart Dimming, Theater 3D, Cinema HDTV, Entertainment Freedom For All, names, phrase and symbols are trademarks or registered trademarks of VIZIO, Inc. All other trademarks may be the property of their respective holders.</p>

<p>SOURCE VIZIO</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>January  5, 2011  8:46 PM</b>
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
			<?=getComments(4124)?>
			<div class="dottedline"></div>

			<? if (7 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4124)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2011/01/hulu-plus-subscription-service-and-blockbuster-on-demand-now-available-on-vizio-internet-apps-adding-more-entertainment-freedom-to-the-industrys-awardwinning-connected-ce-platform.php" type="text/javascript" charset="utf-8"></script>
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