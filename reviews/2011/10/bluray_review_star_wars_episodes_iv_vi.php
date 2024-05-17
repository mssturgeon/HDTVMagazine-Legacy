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
		AND e.entry_id = 4540";
	if ($debug) echo "$sql\n";
	$res_aux = mQuery($sql);
	$row_aux = mysql_fetch_assoc($res_aux);

	$author_title = ($row_aux['title'] == '') ? '' : "{$row_aux['title']}<br />";
	$author_headshot = ($row_aux['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $row_aux['img'] .'" alt="The HT Guys" height="100" width="100"/>';
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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4540 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get author information
/*
	$sql = "SELECT title, channel, amazon_tracking_id, viglink_source, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];
	$viglink_source = $author['viglink_source'];

	if ($debug) echo "Amazon Tracking ID: $amazon_tracking_id\n";
	if ($debug) echo "VigLink Source ID: $viglink_source\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4540 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a
	LEFT JOIN ". TOPICS_TABLE ." t ON (a.topic_id = t.topic_id)
	WHERE a.entry_id = 4540";
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/reviews/2011/10/bluray-review-star-wars-episodes-iv-vi.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

	switch (8) {
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
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4540";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Blu-ray Review: Star Wars (Episodes IV - VI)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Blu-ray Review: Star Wars (Episodes IV - VI)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Blu-ray Review: Star Wars (Episodes IV - VI)" />
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
	<title>HDTV Magazine - Blu-ray Review: Star Wars (Episodes IV - VI)</title>
	<meta name="keywords" content="star wars, audio commentary, ● audio, george lucas, – stars, ●, star, wars, –, audio, stars, george, lucas, commentary, blu, ray, color, cast, disc, movie, interviews, changes, crew, minutes, apx" />
	<meta name="description" content="The May 25, 1977 theatrical debut of Star Wars - on a scant 32 screens across America - was destined to change the face of cinema forever. An instant classic and an unparalleled box office success, the rousing &quot;space opera&quot; was equal parts fairy tale, western, 1930s serial and special effects extravaganza, with roots in mythologies from cultures around the world. From the mind of visionary writer/director George Lucas, the epic space fantasy introduced the mystical Force into the cultural vocabulary, as well as iconic characters such as evil Darth Vader, idealistic Luke Skywalker, feisty Princess Leia, lovable scoundrel Han Solo and wise Obi-Wan Kenobi. Since its 1977 debut, Star Wars has continued to grow, its lush narrative expanding from modest beginnings into an epic, six-film Saga chronicling the fall and redemption of The Chosen One, Anakin Skywalker.


Watching the original trilogy is like hanging out with old friends. It makes it hard to look at these films objectively, but I do look at the movies differently now that I’m an adult. The Star Wars movies are not deep thinking films with something important to say. They’re simply large scale action adventure stories set in space. Where these movie shine is the unique world Lucas has invented, cool looking costumes, and a charismatic cast. These movies made Harrison Ford an instant star. His portrayal of the charming reluctant hero is legendary, and having a princess that can be just as tough as the boys is genius.

Watching these films on Blu-ray..." />
	<meta name="title" content="Blu-ray Review: Star Wars (Episodes IV - VI)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Blu-ray Review: Star Wars (Episodes IV - VI)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/reviews/2011/10/bluray-review-star-wars-episodes-iv-vi.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The May 25, 1977 theatrical debut of Star Wars - on a scant 32 screens across America - was destined to change the face of cinema forever. An instant classic and an unparalleled box office success, the rousing &quot;space opera&quot; was equal parts fairy tale, western, 1930s serial and special effects extravaganza, with roots in mythologies from cultures around the world. From the mind of visionary writer/director George Lucas, the epic space fantasy introduced the mystical Force into the cultural vocabulary, as well as iconic characters such as evil Darth Vader, idealistic Luke Skywalker, feisty Princess Leia, lovable scoundrel Han Solo and wise Obi-Wan Kenobi. Since its 1977 debut, Star Wars has continued to grow, its lush narrative expanding from modest beginnings into an epic, six-film Saga chronicling the fall and redemption of The Chosen One, Anakin Skywalker.


Watching the original trilogy is like hanging out with old friends. It makes it hard to look at these films objectively, but I do look at the movies differently now that I’m an adult. The Star Wars movies are not deep thinking films with something important to say. They’re simply large scale action adventure stories set in space. Where these movie shine is the unique world Lucas has invented, cool looking costumes, and a charismatic cast. These movies made Harrison Ford an instant star. His portrayal of the charming reluctant hero is legendary, and having a princess that can be just as tough as the boys is genius.

Watching these films on Blu-ray..." />
	<meta property="og:site_name" content="HDTV Magazine" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<? if(access(ACCESS_ADMIN)) {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<span class="label">Admin Menu:</span>
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4540', 400, 200);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2011/10/bluray-review-star-wars-episodes-iv-vi.php">Blu-ray Review: Star Wars (Episodes IV - VI)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>October 26, 2011</b>
							</td><td id="article_category">
								Categories: <b><a href="/category.php?id=295&category=Blu-ray">Blu-ray</a></b>
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
				<h2>4.8 Stars (out of 5)</h2> <p>Synopsis</p> <p>The May 25, 1977 theatrical debut of Star Wars - on a scant 32 screens across America - was destined to change the face of cinema forever. An instant classic and an unparalleled box office success, the rousing "space opera" was equal parts fairy tale, western, 1930s serial and special effects extravaganza, with roots in mythologies from cultures around the world. From the mind of visionary writer/director George Lucas, the epic space fantasy introduced the mystical Force into the cultural vocabulary, as well as iconic characters such as evil Darth Vader, idealistic Luke Skywalker, feisty Princess Leia, lovable scoundrel Han Solo and wise Obi-Wan Kenobi. Since its 1977 debut, Star Wars has continued to grow, its lush narrative expanding from modest beginnings into an epic, six-film Saga chronicling the fall and redemption of The Chosen One, Anakin Skywalker.</p> <p>Starring:</p> <p>Ewan McGregor, Hayden Christensen, Natalie Portman, Ian McDiarmid, Samuel L. Jackson, Christopher Lee, Liam Neeson, Jake Lloyd, Ray Park, Anthony Daniels, Kenny Baker, Peter Mayhew, Keisha Castle-Hughes, Jimmy Smits, Ahmed Best</p> <p>Director:</p> <p>George Lucas, Irvin Kershner, Richard Marquand</p> <p>Blu-ray Release Date:</p> <p>September, 16, 2011</p> <p>Subtitles:</p> <p>English SDH, French, Spanish, Portuguese</p> <p>Rating</p> <p>Overall rating weighted as follows:</p> <p><b>Audio 40%, Video 40%, Special Features 20%</b>, <b>Movie - its just our opinion so take it with a grain of salt</b></p> <h2>Audio 4.9 Stars (out of 5)</h2> <p><i>Dolby and DTS Demo Discs used as basis for comparison</i></p> <p>● Subwoofer – <b>5.0 Stars</b></p> <p>● Dialog – <b>5.0 Stars</b></p> <p>● Surround Effects – <b>5.0 Stars</b></p> <p>● Dynamic Range – <b>4.5 Stars</b></p><b></b> <p><b>English:</b> DTS-HD Master Audio 6.1, <b>Spanish:</b> Dolby Digital 5.1, <b>French:</b> DTS 5.1, <b>French:</b> Dolby Digital 5.1, <b>Portuguese: </b>Dolby Digital 5.1<b></b></p> <p>The original Star Wars trilogies are over 30 years old, and don’t show their age in this audio performance. These movies are definitely the perfect choice to show off your home theater system. The Millennium Falcon zooms past all channels with heavy bass, the explosions of the Death Star’s are enveloping and rumble like earthquakes, AT-AT and Rancor footsteps shake the shake the floor, and the famous John Williams score pumps through all channels. Rear speakers have continuous action, whether it be heavy spaceships flying buy, or subtle sounds of Ewok chatter. Overall, when comparing the prequels to the originals is a little flat, but it definitely outshines most other Blu-rays.</p> <h2>Video 4.7 Stars (out of 5)</h2> <p><i>Spears &amp; Munsil Benchmark Blu-ray Edition used as basis for comparison</i></p> <p>● Color Accuracy - <b>5.0 Stars</b></p> <p>● Shadow detail – <b>4.5 Stars</b></p> <p>● Clarity – <b>4.5 Stars</b></p> <p>● Skin tones – <b>5.0 Stars</b></p> <p>● Compression – <b>5.0 Stars</b></p> <p><b>Video codec: </b>MPEG-4 AVC, <b>Video resolution:</b> 1080p, <b>Aspect ratio: </b>2.35:1, <b>Original aspect ratio:</b> 2.39:1<b></b></p> <p>The clarity of these films are better than most older films converted to Blu-ray. Its clean and clear enough to notice the monstrous wrinkles in the Emperors face, long Wookie hairs, grime on the droids, and Harrison Ford’s chin scar. Just like the prequels, dark scenes lose some detail in the shadows, there is more film grain here but it isn’t too much. The colors are slightly warm and help bring out the beautiful colors in the sunset of Cloud City, and the lush green forests of Endor.</p> <h2>Bonus Features 5.0 Stars (out of 5)</h2> <p>DISC ONE – STAR WARS: EPISODE I THE PHANTOM MENACE</p> <p>● Audio Commentary with George Lucas, Rick McCallum, Ben Burtt, Rob Coleman, John Knoll, Dennis Muren and Scott Squires</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC TWO – STAR WARS: EPISODE II ATTACK OF THE CLONES</p> <p>● Audio Commentary with George Lucas, Rick McCallum, Ben Burtt, Rob Coleman, Pablo Helman, John Knoll and Ben Snow</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC THREE – STAR WARS: EPISODE III REVENGE OF THE SITH</p> <p>● Audio Commentary with George Lucas, Rick McCallum, Rob Coleman, John Knoll and Roger Guyett</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC FOUR – STAR WARS: EPISODE IV A NEW HOPE</p> <p>● Audio Commentary with George Lucas, Carrie Fisher, Ben Burtt and Dennis Muren</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC FIVE – STAR WARS: EPISODE V THE EMPIRE STRIKES BACK</p> <p>● Audio Commentary with George Lucas, Irvin Kershner, Carrie Fisher, Ben Burtt and Dennis Muren</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC SIX – STAR WARS: EPISODE VI RETURN OF THE JEDI</p> <p>● Audio Commentary with George Lucas, Carrie Fisher, Ben Burtt and Dennis Muren</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC SEVEN – NEW! STAR WARS ARCHIVES: EPISODES I-III</p> <p>● Including: deleted, extended and alternate scenes; prop, maquette and costume turnarounds; matte paintings and concept art; supplementary interviews with cast and crew; a flythrough of the Lucasfilm Archives and more</p> <p>DISC EIGHT – NEW! STAR WARS ARCHIVES: EPISODES IV-VI</p> <p>● Including: deleted, extended and alternate scenes; prop, maquette and costume turnarounds; matte paintings and concept art; supplementary interviews with cast and crew; and more</p> <p>DISC NINE – THE STAR WARS DOCUMENTARIES</p> <p>● Star Warriors (2007, Color, Apx. 84 Minutes) – Some Star Wars fans want to collect action figures...these fans want to be action figures! A tribute to the 501st Legion, a global organization of Star Wars costume enthusiasts, this insightful documentary shows how the super-fan club promotes interest in the films through charity and volunteer work at fundraisers and high-profile special events around the world.</p> <p>● A Conversation with the Masters: The Empire Strikes Back 30 Years Later (2010, Color, Apx. 25 Minutes) – George Lucas, Irvin Kershner, Lawrence Kasdan and John Williams look back on the making of The Empire Strikes Back in this in-depth retrospective from Lucasfilm created to help commemorate the 30th anniversary of the movie. The masters discuss and reminisce about one of the most beloved films of all time.</p> <p>● Star Wars Spoofs (2011, Color, Apx. 91 Minutes) – The farce is strong with this one! Enjoy a hilarious collection of Star Wars spoofs and parodies that have been created over the years, including outrageous clips from Family Guy, The Simpsons, How I Met Your Mother and more — and don’t miss “Weird Al” Yankovic’s one-of-a-kind music video tribute to The Phantom Menace!</p> <p>● The Making of Star Wars (1977, Color, Apx. 49 Minutes) – Learn the incredible behind-the-scenes story of how the original Star Wars movie was brought to the big screen in this fascinating documentary hosted by C-3PO and R2-D2. Includes interviews with George Lucas and appearances by Mark Hamill, Harrison Ford and Carrie Fisher.</p> <p>● The Empire Strikes Back: SPFX (1980, Color, Apx. 48 Minutes) – Learn the secrets of making movies in a galaxy far, far away. Hosted by Mark Hamill, this revealing documentary offers behind-the-scenes glimpses into the amazing special effects that transformed George Lucas’ vision for Star Wars and The Empire Strikes Back into reality!</p> <p>● Classic Creatures: Return of the Jedi (1983, Color, Apx. 48 Minutes) – Go behind the scenes — and into the costumes — as production footage from Return of the Jedi is interspersed with vintage monster movie clips in this in-depth exploration of the painstaking techniques utilized by George Lucas to create the classic creatures and characters seen in the film. Hosted and narrated by Carrie Fisher and Billie Dee Williams.</p> <p>● Anatomy of a Dewback (1997, Color, Apx. 26 Minutes) – See how some of the special effects in Star Wars became even more special two decades later! George Lucas explains and demonstrates how his team transformed the original dewback creatures from immovable rubber puppets (in the original 1977 release) to seemingly living, breathing creatures for the Star Wars 1997 Special Edition update.</p> <p>● Star Wars Tech (2007, Color, Apx. 46 Minutes) – Exploring the technical aspects of Star Wars vehicles, weapons and gadgetry, Star Wars Tech consults leading scientists in the fields of physics, prosthetics, lasers, engineering and astronomy to examine the plausibility of Star Wars technology based on science as we know it today.</p> <h2>Movie – 4.0 Stars (out of 5)</h2> <p>Review</p> <p>Watching the original trilogy is like hanging out with old friends. It makes it hard to look at these films objectively, but I do look at the movies differently now that I’m an adult. The Star Wars movies are not deep thinking films with something important to say. They’re simply large scale action adventure stories set in space. Where these movie shine is the unique world Lucas has invented, cool looking costumes, and a charismatic cast. These movies made Harrison Ford an instant star. His portrayal of the charming reluctant hero is legendary, and having a princess that can be just as tough as the boys is genius.</p> <p>Watching these films on Blu-ray with my kids warms my heart. Every time my daughter sees Luke get his hand cut off by Darth Vader her eyes grow big and she flinches. After watching a Return of the Jedi, my son grabs his toy green light saber and tosses me the red one and screams, "I'll never join you!". That's the magic of Star Wars, it's timeless. Somehow it captures a child’s imagination and feeds it fantasy and heroism. It can also remind an adult what it was like to be a kid.</p> <p>There are several changes to the blu-ray version of this film that have fanboys on the internet loosing their minds. It's gotten so bad that Amazon user reviews have only gotten an average of 2 stars out of 5. However, it hasn't stopped the Blu-rays from selling, in fact the Star Wars Saga is the fasting selling Blu-ray in history. Let's go over some of the Blu-ray changes.</p> <p>- Ewoks eyes blink now - I like this. Living teddybears with unblinking lifeless eyes freak me out.</p> <p>- Obi-Wan Kenobi's screams at the sand people to scare them away - When I first head this in the movie I laughed because it sounded ridiculous.</p> <p>- Darth Vader screams no when he throws the Emperor over the railing - This was completely unnecessary. Everyone knows what Vader was thinking when he saved his son.</p> <p>There are a few more, but I just want to get this point across. I don't think these changes ruin the movie. Did he have to make these changes? Of course not. Did these changes improve the movie? No. Honestly there aren't that many Blu-ray changes. Most of the major changes were on the DVD release and that's the version my children will remember. Even if I showed them my VHS tapes of the original versions, the small changes won't make a difference to them, so why should it make a difference to me?</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>October 26, 2011  7:44 AM</b>
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
			<?=getComments(4540)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('The HT Guys', 4540)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author_bio != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2011/10/bluray-review-star-wars-episodes-iv-vi.php" type="text/javascript" charset="utf-8"></script>
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