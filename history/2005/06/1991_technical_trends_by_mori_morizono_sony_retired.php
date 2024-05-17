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
		AND e.entry_id = 126";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 126 AND placement_is_primary = 1";
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 126 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 126";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/history/2005/06/1991_technical_trends_by_mori_morizono_sony_retired.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 126";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download 1991 -Technical Trends by Mori Morizono, Sony (Retired)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="1991 -Technical Trends by Mori Morizono, Sony (Retired)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="1991 -Technical Trends by Mori Morizono, Sony (Retired)" />
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
	<title>HDTV Magazine - 1991 -Technical Trends by Mori Morizono, Sony (Retired)</title>
	<meta name="keywords" content="signal processing, solid state, recording technology, state technology, bit rate, recording, technology, processing, signal, time, possible, bit, our, optical, per, signals, new, future, computer, been, information, may, solid, state, digital" />
	<meta name="description" content="I present here remarks from one of Japan's most celebrated technologist, &quot;Mori&quot; Morizono, Sony (now retired). He spoke on June 11, 1987 (yes, I said 1987) at the Montreux Symposium, Montreux, Switzerland. Only a part of his far-reaching vision has..." />
	<meta name="title" content="1991 -Technical Trends by Mori Morizono, Sony (Retired)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="1991 -Technical Trends by Mori Morizono, Sony (Retired)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/history/2005/06/1991_technical_trends_by_mori_morizono_sony_retired.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="I present here remarks from one of Japan's most celebrated technologist, &quot;Mori&quot; Morizono, Sony (now retired). He spoke on June 11, 1987 (yes, I said 1987) at the Montreux Symposium, Montreux, Switzerland. Only a part of his far-reaching vision has..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=126', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/history/2005/06/1991_technical_trends_by_mori_morizono_sony_retired.php">1991 -Technical Trends by Mori Morizono, Sony (Retired)</a></td>
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
				<p><em>I present here remarks from one of Japan's most celebrated technologist, "Mori" Morizono, Sony (now retired). He spoke on June 11, <strong><em>1987 </em></strong> (yes, I said 19<u>87</u>) at the Montreux Symposium, Montreux, Switzerland. Only a part of his far-reaching vision has so far been realized commercially. There is much more to go. "It is my feeling, he said, "that any limitations we perceive are self-imposed." I decided to place this document on our web site as a reminder of how far ahead our big technology firms can be. You will read in this paper things which are just now becoming mainstream, like perpendicular recording. This is in some ways a proven road map to the future. </p>

<p>I had the good fortune of meeting with Mr. Morizono in Sony's Headquarters in Atsugi, Japan. From the moment I stepped into their imposing granite structure I knew I had entered a different world, one many generations ahead of that which was roaring in the streets. Everything operated like a Swiss clock movement and NOTHING was out of place nor out of time. It was a most memorable experience. Excuse me for that personal departure and let me refocus you now to what I felt was a significant presentation for my development. I am well aware that progress has since come in leaps and bounds and as we identify important addresses I will post them here for permanent reference. I will add to this section that my visit to Sony and Morizono, in particular, was to get a sense of how prepared the manufacturers were then to the producing of commercial grade HDTV monitors and receivers. The assurances were complete and I came back to the United States fully confident that we could make the transition because the way to hardware building was already known. Few consumers realize that the HDTV initiative did NOT come from the  manufacturers. It came independantly from NHK, the national broadcaster of Japan. The manufacturers were very leary of HDTV and only participated in it because of a fear that if they didn't another fierce competitor might and thus steal their thunder (marketshare) should it ever come about. While they were all very nervous over the business prospects for introducing HDTV to the masses some were superbly prepared from the perspective of technology. </em></p>

<p></p>

<p>15TH INTERNATIONAL TELEVISION SYMPOSIUM<br />
MONTREUX SWITZERLAND</p>

<p>JUNE 11, 1987</p>

<p>KEYNOTE ADDRESS OF </p>

<p>MR. MASAHIKO MORIZONO</p>

<p><br />
<strong>"TECHNOLOGICAL TRENDS"</strong></p>

<p><br />
_____________________________________________________________________</p>

<p>"It is my feeling that any limitations we perceive are self-imposed."<br />
_____________________________________________________________________</p>

<p><br />
Mr. Morizono...</p>

<p>I remember attending the Montreux International Symposium for the first time in 1975, and again in 1977 when we participated in the Technical Exhibition with our ENG products. In the 10 years since that time, technoigy has made rapid progress. In particular, Broadcasting equipment technology has enjoyed great advances, especially in the areas of CATV and Satellite Broadcasting, Overall, the technological environment has been steadily changing from analog to digital.</p>

<p>The Symposium Executive Committee has requested me to talk about "Technological Trends". The scope of technnology to be covered at this Symposium is very broad. It would be impossible for me to even begin covering all its aspects at this symposium, so I have decided to limit myself to a few specific examples which I feel illustrate the general "technological trends". Please bear in mind that I will be stating my personal views as a Broadcasting Equipment manufacturer and as the person responsible for all Research and Development at Sony Corporation.</p>

<p>The technological fields which will be covered at this Symposium can be roughly classified as follows-</p>

<p>- telecommunication</p>

<p>- transmission and receiving technology (including satellite broadcasting)</p>

<p>- signal processing</p>

<p>- opto-electronics</p>

<p>- recording technology (including media and devices for recording and playback)</p>

<p>-computer technology (including hardware and software)</p>

<p>-display and sensor technology</p>

<p>-solid-state technology.</p>

<p>I would like to express my opinion on some of those technologies, especially signal processing, recording technology, recording media, and solid-state technology, whose development I believe are the key factor for the future progress of hardware to be found in forthcoming broadcasting equipment.</p>

<p><strong>Solid-State Technology</strong></p>

<p>Let me begin with an overview of the trend Solid-State Technology has been displaying. Solid-State Technology is at the heart of the electronics industry. It is generally accepted that the degree to which we can integrate circuits will determine success or failure in this industry.</p>

<p>Large Scale Integration plays a key role in one of the important trends of solid-state technology, which is the rapid change from analog to digital processing. This change means that a great deal of information must be transferred from analog to digital. In order to accomplish this, faster ways to process information will need to be developed, Along these same lines, signal processing will require advances in large-scale integration technology so that more efficient input/output interfacing from analog to digital can be accomplished,</p>

<p>At present, image/video signals are quantized at 8 bits per samples, at sampling frequencies from 100 MHz to 300 MHz. Audio signals are normally quantized to 16 bits per sample at 48 kHz. In the future, however, 10 to 12 bits per sample quantization at a sampling frequency of 500 MHz to 1GHZ for image/video signals, and a quantization of 20 or more bits per sample for audio signals at frequencies over 100 kHz, will become necessary for higher quality signal processing. As the number of quantization levels increases, circuit integration and operating speed must increase as well if CPU'S, multiplier-adders, memory devices and ASIC's (Application Specific Integrated Circuits) are to be realized-</p>

<p>Increased circuit integration cannot be accomplished without further advances in sub-micron technology. At the moment, our highest level of technology is defined by the µpm design rule. However, a 0.1-0.2 pm design rule is anticipated when SOR (Synchrotron Orbital Radiation) and Excimer Laser Lithography technology become advanced enough for practical use. The possible applications for these Sub-micron technologies are astonishing. For example, a D-RAM of 64 Mbit/chip to 128 Mbit/chip is entirely possible. With the advent of such devices, a solid-state audio recorder is sure to become a reality. If we extend this technology even further into the 3-dimensional realm, the LSI structure may allow us to increase the capacity of the D-RAM tremendously. Naturaily, higher operating speeds and lower power consumption can also be expected.</p>

<p>In order for ultra-high-speed information processing to be accomplished, both the GaAs and the superconductive material technologies are quite important. In the future, the combination of these two technologies may allow us to achieve operating speeds which are 10 to 20 times faster than those presently possible with silicon devices.</p>

<p>Application-specific IC devices have great potential for use in signal processing, which I will touch upon later.</p>

<p>I would also like to mention here the increasing importance of new IC memory devices. Recently there have been some exciting developments which indicate that Photo-chemical Hole-Buuring Memory , Bloch Line Memory and Molecular Memory devices may be possible. Although Photo-chemical Hole-Burning, for example, is still in the research stage, it is anticipated that it will be possible to write and read approximately 103 to 104 bits of information per laser beam spot, utilizing the wavelength selectivity of a material such as Porphiline. Assuming that the laser beam spot size is less than 1 µm2 it will be possible to record and retrieve approximately 100 Gigabits to 1 Terabit of information using only 1 cm2 or 100 million µm2 of active memory area.</p>

<p>The recording data rate of the present 4:2:2 digital VTR is 216 Mbit/sec, and its total number of bits per hour is therefore 216 Mbits/sec x 60 x 60 = 777.6 Gigabits. Thus, according to this rough calculation, it will be possible to record and play back video information equivalent to more than one hour of 4:2:2 digital VTR playing time using only 1 cm2 of a photo-chemical Hole Burning memory chip active area.</p>

<p><strong>Signal Processing</strong></p>

<p>Now I would like to speak about the future of signal processing technology. It is my belief that the effective application of bit rate reduction is one of the most important aspects of signal processing at this time. As you know, bit reduction technology is applied to two areas of signal processing. the transmission of signals, and their recording and playback.</p>

<p>Until now, we have been quite successful in our efforts to process signals at the baseband frequency, and bit rate processing was not needed in past applications. However, it is not economically feasible to transmit high-quality signals in full bandwidth because of restrictions in the frequency band allocation. We will have to develop new bit rate reduction algorithms which can give us higher-quality audio and video signal transmission.</p>

<p>In the recording field, bit rate reduction is extremely important also, since recording density is limited by the physical interface between a device and its recording medium. It is desirable, from an economic point of view, to record a high-quality signal in as small an area as possible with no degradation, impairment or aliasing.</p>

<p>Bit rate reduction has applications in the computer graphics field as well, although the constraints are not the same as in television signal processing, since television signals are generally more correlative than computer graphics signals. DPCM, Cosine Transtorm, K-L Transform, Vector Quantization, and ADRC (Adaptive Dynamic Range Coding) are now available. However, these algorithms are not sufficient to eliminate degradation in computer Graphics. The development of an algorithm which allows us to take full advantage of computer graphics should be a top priority.</p>

<p>Another important aspect of signal processing is picture processing, particularly picture manipulation. Let's take an example. At the present time, it is possible, using expensive and cumbersome hardware, to use signal processing to manipulate or alter an object in a picture which appears during one T.V. field scanning period. We can eliminate that object, change its color or deform its shape. Eventually, as our high speed solid-state device and parallel processing architecure technologies are perfected, this kind of picture manipulation can be accomplished by CPU and DSP (Digital Signal Processing) chips at speeds of 15 GIPS (15 Giga-Instructions Per Second) or faster. Such chips will also allow real-time picture processing to become common-place, and high-quality, special T.V. signal effects, such as Chroma-key, to be implemented with greatly improved picture quality.</p>

<p><strong>Recording</strong></p>

<p>Now, let's consider recording technology. Until recently, magnetic recording has been the only practical recording technology for long-time picture recording and playback in the broadcasting field, and it will continue to be used as the main recording technology for quite some time. As a result, a lot of research has naturally been focused on magnetic recording, resulting in advances such as the development of MP (metal-particle) and ME (metal-evaporated) tapes and a greatly reduced recording area per bit. ME tape technology, for example, has made it possible to record at only 1 bit per 3.5 µm2.</p>

<p>Looking to the future, perpendicular recording promises even higher recording densities. It will definitely be possible, for example, using Co-Cr tape as the recording medium, to achieve a recording density of 1 bit per 1 µm2. Together with bit reduction, and in the case of an NTSC signal, one will then achieve approximately 6 hours of recording and playback at a bit rate of 30 Mbit/sec with good picture quality using 8mm video tape.</p>

<p>Today, magnetic recording is no longer the only available recording technology. The optical disc with Laser diode recording and playback has opened a whole new area of possibilities. As you know, the CD (Compact Disc) has had a great effect on the music recording industry, and is currently being used as a data storage medium in applications such as CD-ROM (Read-Only Memory).</p>

<p>Other types of optical discs are also being used for mass storage. These include the WO (Write-Once) disc, the Dye Polymer disc and the Magneto-optical disc which utilizes the Kerr Effect. The Dye Polymer and Magneto-optical discs make it possible for information to be recorded, read out and erased. The magneto-optical disc has a recording capacity of approximately 700 Mbytes when using both sides of a 5 1/4 " (13cm) disk. If the disk's diameter is extended to 30cm) recording capacity increases to 4 Gigabytes when both sides are usd in the CLV (Constant Linear Velocity) mode. In practical terms, this means that 216 Mbits/sec of 4:2:2 digital video signal can be recorded for approximately 5 minutes,</p>

<p>As impressive as this storage capacity is, it is nevertheless limited at the present time by the 780-830 nm wavelength of the laser diode used, and by the fact that recording is done only with two levels. In the future, it may be possible to shorten the wavelength of the laser diode and to achieve multi-level recording, thereby increasing recording capacity at least 4 times. If this is achieved, recording time for 4:2:2 signals will be more than 20 minutes for a 30cm diameter disk and long-time record/playback, in multi-platter operation, will be possible.</p>

<p>The most important advantage of optical disk recording over magnetic tape recording is its high-speed access capability and very short seek time. This makes it the ideal recording medium for of f-line editing and f inal program assembly. Therefore, while the optical disk will not replace the VTR, it is certain to play an important role in recording, particularly in post-production applications.</p>

<p>Recording technology researchers are currently exploring new recording systems. A new and very promising one is the optical video tape recorder which would use an optical medium and eventually provide a recording density of 100 Mbits/cm2 to 400 Mbits/cm2. The realization of a system such as this will mean that we have entered a new technological era in which a choice must be made between magnetic and optical tape recording and optical disk recording.</p>

<p>Although I have been involved in standardization activities for a long time now, in the so-called "Format Battles", I will have retired by the time the next era begins. I will observe your struggles to apply the new technology with great interest from the sidelines. I will be like an enthusiastic football fan cheering on his team,</p>

<p><strong>Reaching Out</strong></p>

<p>In closing, I would like to take a look at the future of technology from my own personal perspective. To the average scientist, working in the fields of integrated circuitry or computer technology, there may seem to be a limit to what we can achieve, even though, for example, new computer architectures (the so-called non-von-Neuman machines), have been proposed as one possible way to increase our options in those areas. It is my feeling that any limitations we perceive are self-imposed.</p>

<p>It is time to reach beyond what we can immediately perceive as feasible with our present-day technology, and to extend our questioning and experimentation to include the exploration of molecular mechanisms. In particular, the transfer of information via proteins in living organisms is a scientific phenomenen which should excite our interest, Though it may be difficult for us to conceive how this mechanism could be used to our advantage scientifically, we should not dismiss it lightly. While exploring the functions of living matter, we can find clues which may lead to new discoveries. For example, protein information transfer could have a great influence on computer electronics and therefore computer technology. This in turn would influence the broadcasting industry. This is why I believe that bio-electronics may be the key to future technological breakthroughs in the 21st century. I urge you to keep your minds open, and continue to reach out and explore new possibilities. There is no limit to what we can achieve. Thank you for your kind attention.</p>

<p><br />
</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Dale Cripps</b>, <b>June 26, 2005  8:06 AM</b>
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
			<?=getComments(126)?>
			<div class="dottedline"></div>

			<? if (5 != 7) echo getBoxMoreFromAuthor('Dale Cripps', 126)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1991_technical_trends_by_mori_morizono_sony_retired.php" type="text/javascript" charset="utf-8"></script>
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