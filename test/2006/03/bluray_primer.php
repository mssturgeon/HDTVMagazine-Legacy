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
		AND e.entry_id = 348";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 348 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 348 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 348";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2006/03/bluray-primer.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 348";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Blu-ray Primer" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Blu-ray Primer" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Blu-ray Primer" />
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
	<title>HDTV Magazine - Blu-ray Primer</title>
	<meta name="keywords" content="blu ray, sony pictures, drives offer, mgm sony, century fox, dvd, ray, blu, sony, pictures, release, disc, much, lionsgate, samsung, offer, laser, drives, available, internal, titles, mgm, external, speed, formats" />
	<meta name="description" content=" For those of you that read the HD DVD Primer article published a few weeks back, you will find this article is of similar style and content. Now we turn to the competing HD disc format: Blu-ray Disc (BD)...." />
	<meta name="title" content="Blu-ray Primer" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Blu-ray Primer" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2006/03/bluray-primer.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content=" For those of you that read the HD DVD Primer article published a few weeks back, you will find this article is of similar style and content. Now we turn to the competing HD disc format: Blu-ray Disc (BD)...." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=348', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2006/03/bluray-primer.php">Blu-ray Primer</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>March  8, 2006</b>
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
				<div style="float:left;width:175px">
<object type="application/x-shockwave-flash" width="160" height="100" data="/images/articles/blu-ray.swf">
	<param name="movie" value="/images/articles/blu-ray.swf"></param>
	<param name="quality" value="high" /><param name="WMODE" value="OPAQUE" />
</object>
</div>For those of you that read the HD DVD Primer article published a few weeks back, you will find this article is of similar style and content. Now we turn to the competing HD disc format: Blu-ray Disc (BD). This article covers the basics of Blu-ray audio and video, gives a brief overview of the models arriving in May, and concludes with a listing of BD movies that will be available upon release (and soon thereafter).

<p>Roughly two month's after the HD DVD debut here in the U.S., the Blu-ray Disc (BD) format will enter the scene. On May 23rd, a (relative) slew of BD titles will be available from Lionsgate and Sony, with a second set of titles arriving on June 13th. 20th Century Fox and Disney have yet to announce anything as of this writing, but this article will be updated as soon as they do. Pricing for these scheduled releases seems to settle out around $40 for new releases and $25 for catalog titles. You can check the table below for scheduled releases, dates, prices, etc. This represents almost a 50% increase from what most are paying for standard DVD's these days. Will you pay that much of a premium for these titles and the added functionality that comes with them? Feel free to let me know your thoughts via the comments at the end.</p>

<p>Samsung and Pioneer are planning to release their Blu-ray players to coincide with this late-May release. Samsung will be releasing the BD-1000 and Pioneer will release the BDP-HD1. Pioneer also plans to release their BDR-101A PC Drive by the end of March. And Samsung recently announced a pair of BD burners (internal and external) for the PC, which will be available in April. Sony's BDP-S1 stand-alone Blu-ray player will not likely make the May debut, releasing it along with a BD compatible VAIO PC a bit later in the year. The Philips BD player, the BDP 9000, also will not likely arrive at the same time as the Samsung and Pioneer players, instead arriving later in the second half of this year. No pricing has been made available yet aside from an approximate $1000 price-point.</p>

<p>The delay in release of the Sony stand-alone player raises some speculation as to whether the PS3 will be ready prior to this fall/winter. Originally, Sony was to introduce the first of its BD titles in conjunction with the PS3, and not release a stand-alone player until much later to give the PS3 time to gain a good share of the market.</p>

<p>The most exciting among all the hardware is LG's Universal Blu-ray, HD DVD Player. The BD199 is planned for a late-summer/early-fall release, which means it will probably be available just in time for the Holiday buying season.</p>

<p><br />
<h2>Video Basics</h2><br />
<img src="/images/articles/spectrum.gif" alt="Laser Spectrum" align="right">Blue-ray discs can store up to 27GB per layer, compared to HD DVDs 15GB and standard DVDs 4.7GB. This increased storage capacity is due to the new blue-violet laser that is used to read from and write to the disc. Traditional DVDs utilized a red laser. This new blue-violet laser has a 405nm wavelength vs. the red laser's 650nm. This shorter wavelength allows for much higher data density since the blue laser can write a much narrower data track. The net effect is that Blu-ray and HD DVD can store more than 3 times the number of bits as traditional DVDs. So you might ask yourself:</p>

<p><b>Why then does Blu-ray have a much higher capacity than HD DVD if they use the same laser?</b></p>

<p><img src="/images/articles/bd_dvd_cd_laser.gif" align="right"><br><br />
<img src="/images/articles/bd_dvd_cd_pits.gif" align="right"><br />
The laser head on a Blue-ray drive comes much closer to the recording medium than it does in an HD DVD player, allowing for significantly improved tolerances and much improved precision. The benefit is that the pits are even smaller for Blue-ray than for HD DVD, allowing for even greater data density. This is illustrated via the images at right, which compare Blu-ray to traditional DVD and CD. The down-side is that you then have a much thinner protective resin on the surface of the disc, which would be much more suceptible to scratches and damage. To work around this, BD media originally called for a caddy to protect the disc. This was eliminated when a new material was found that could provide the same level of scratch protection in a much thinner coating. From the BDA website:</p>

<blockquote>As the result of recent breakthroughs in the development of hard coating for Blu-ray Disc, the discs offer much stronger resistance to scratches and fingerprints than other existing and proposed formats. Hard coated Blu-ray Discs do not require a cartridge and can be used as a bare disc, similar to DVD and CD.</blockquote>

<p></p>

<h2>Audio Basics</h2>

<p><br />
<h2>Hardware Comparison</h2></p>

<p>"The BD-P1000 will pump out HD content at 720p or 1080i resolutions, Samsung said. Supported audio formats include 192KHz LPCM, Dolby Digital and Dolby Digital Plus, MPEG 2, DTS, and MP3. The machine will play users' existing DVD and CD libraries, along with content stored on DVD-RAM and DVD±R/RW discs, and it has a memory card reader capable of taking Compact Flash, XD, Micro Drive, SD, MMC and RS-MMC, and MemoryStick and Memory Stick Duo cards. Ports built into the device include CVBS Output, S-Video Output, component output, HDMI, and both digital and analog audio outputs."</p>

<p>"Samsung Electronics officialy announed the release of an internal (SH-B022A) and external (SE-B026A) Blu-ray burner for April 2006. The drives will offer users the ability to store up to 50GB onto a dual layer disc. On top of the HD recording on BD, the Samsung Blu-ray drives offer reading and writing of conventional DVD's and CD's by using a set of two lenses and 3 laser diodes. Compatible with legacy DVD and CD formats, the drives offer HD video burning and reading at a 2x speed (9MB/s). Specifically, the internal SH-B022A supports 12x for DVD and 40x for CD, while the external SE-B026A offers the same speed for DVD and a 32x for CD."</p>

<p>"The Pioneer BDR-101A supports the Blu-ray BD-R, BD-RE, and BD-ROM specifications for writable, re-writable and read-only disks, respectively. It is also compatible with all DVD formats except for DVD-RAM. Availability is planned for the end of March. The first generation Blu-ray disks can hold up to 50GB in two layers. However, while the BDR-101A can read dual-layer BD-ROMs, it only supports single-layer writing. Future drives will support four, six, or eight layers, fitting up to 200GB on a single disk. The BDR-101A does not support standard CDs, but this will be added in the next generation along with DVD-RAM compatibility, Sheridan said. It is expected to cost £340 - £400 initially, while rewritable media is expected to cost about £10 each."</p>

<p>"Samsung Electronics officialy announed the release of an internal (SH-B022A) and external (SE-B026A) Blu-ray burner for April 2006.</p>

<p>The drives will offer users the ability to store up to 50GB onto a dual layer disc. On top of the HD recording on BD, the Samsung Blu-ray drives offer reading and writing of conventional DVD's and CD's by using a set of two lenses and 3 laser diodes.</p>

<p>Compatible with legacy DVD and CD formats, the drives offer HD video burning and reading at a 2x speed (9MB/s). Specifically, the internal SH-B022A supports 12x for DVD and 40x for CD, while the external SE-B026A offers the same speed for DVD and a 32x for CD."</p>

<p><img src="Samsung_BDDrives.jpg"></p>

<p>"Samsung Electronics officialy announed the release of an internal (SH-B022A) and external (SE-B026A) Blu-ray burner for April 2006.</p>

<p>The drives will offer users the ability to store up to 50GB onto a dual layer disc. On top of the HD recording on BD, the Samsung Blu-ray drives offer reading and writing of conventional DVD's and CD's by using a set of two lenses and 3 laser diodes. Compatible with legacy DVD and CD formats, the drives offer HD video burning and reading at a 2x speed (9MB/s). Specifically, the internal SH-B022A supports 12x for DVD and 40x for CD, while the external SE-B026A offers the same speed for DVD and a 32x for CD."</p>

<p></p>

<p>Slated for release next month are the following Blu-ray players:</p>

<p></p>

<p>Other hardware planned for later this year are as follows:</p>

<p></p>

<h2>Software</h2>

<p><br />
<h2>Titles</h2></p>

<p>Below is a list of titles expected to be available this year, with the expected date listed for those that had it available. Those films listed below as links are <strong>available for pre-order</strong>.</p>

<table class="type1b">
	<tr><td class="type1b_header">Title</td><td class="type1b_header">Date</td><td class="type1b_header">Studio</td><td class="type1b_header">Price</td></tr>
	<tr><td class="grid"><a href="">Crash</a></td><td class="grid">5/23/2006</td><td class="grid">Lionsgate</td><td class="grid">$39.99</td></tr>
	<tr><td class="grid"><a href="">Lord of War</a></td><td class="grid">5/23/2006</td><td class="grid">Lionsgate</td><td class="grid">$39.99</td></tr>
	<tr><td class="grid"><a href="">The Punisher</a></td><td class="grid">5/23/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">Saw</a></td><td class="grid">5/23/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">Terminator 2: Judgement Day</a></td><td class="grid">5/23/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">50 First Dates</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">House of Flying Daggers</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">The Last Waltz (MGM)</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Underworld Evolution</a></td><td class="grid">"Early Summer"</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">The Fifth Element (MGM)</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Hitch</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">A Knight's Tale</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Resident Evil Apocalypse (MGM)</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">XXX (MGM)</a></td><td class="grid">5/23/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Reservoir Dogs</a></td><td class="grid">6/13/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">Total Recall</a></td><td class="grid">6/13/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">Stargate</a></td><td class="grid">6/13/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">Frank Herbert's Dune</a></td><td class="grid">6/13/2006</td><td class="grid">Lionsgate</td><td class="grid">$29.99</td></tr>
	<tr><td class="grid"><a href="">The Devil's Rejects</a></td><td class="grid">6/13/2006</td><td class="grid">Lionsgate</td><td class="grid">$39.99</td></tr>
	<tr><td class="grid"><a href="">Kung Fu Hustle</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid"></td></tr>
	<tr><td class="grid"><a href="">Legends of the Fall</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Robocop (MGM)</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Stealth</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Species (MGM)</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">SWAT</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">Terminator (MGM)</a></td><td class="grid">6/13/2006</td><td class="grid">Sony Pictures HE</td><td class="grid">~$18-24</td></tr>
	<tr><td class="grid"><a href="">The Fantastic Four</a></td><td class="grid">2006</td><td class="grid">20th Century Fox</td><td class="grid"></td></tr>
	<tr><td class="grid"><a href="">The League of Extraordinary Gentlemen</a></td><td class="grid">2006</td><td class="grid">20th Century Fox</td><td class="grid"></td></tr>
	<tr><td class="grid"><a href="">Behind Enemy Lines</a></td><td class="grid">2006</td><td class="grid">20th Century Fox</td><td class="grid"></td></tr>
	<tr><td class="grid"><a href="">Ice Age</a></td><td class="grid">2006</td><td class="grid">20th Century Fox</td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
	<tr><td class="grid"><a href=""></a></td><td class="grid"></td><td class="grid"></td><td class="grid"></td></tr>
</table>

<p><br />
<h2>Where can you try it out?</h2></p>

<p></p>

<h2>References</h2>

<ul><li><a href="/cgi-bin/ntlinktrack.cgi?http://www.blu-raydisc.com/">The Blu-ray Disc Association (BDA)</a></li></ul>
<strong>
Editor's Note</strong>: This is intended to be a living document, so if you have any additions, suggestions, or comments, please let me know via the "Comments" section below.
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>March  8, 2006  3:47 PM</b>
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
			<?=getComments(348)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 348)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2006/03/bluray-primer.php" type="text/javascript" charset="utf-8"></script>
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