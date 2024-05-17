<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	require(BASE_DIR .'/includes/lib_amazon.php');
	require(BASE_DIR .'/includes/lib_pg.php');

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-type: text.plain');

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
	$sql = "SELECT title, channel, amazon_tracking_id, img, bio_short
	FROM aux_author au, mt_author a
	WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'";
	if ($debug) echo "$sql\n";
	$res_author = mQuery($sql);
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="Shane Sturgeon" height="100" width="100"/>';
	$amazon_tracking_id = ($author['amazon_tracking_id'] != '') ? $author['amazon_tracking_id'] : $admindata['amazon_associates_id'];
	$google_links_channel = $author['channel'];

	if ($debug) echo "$amazon_tracking_id\n";

	# Get category information
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4538 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4538
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2011/10/bluray-review-star-wars-episodes-iiii.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
				# Moved to footer-4
#			$apture = '<script id="aptureScript" type="text/javascript" src="http://www.apture.com/js/apture.js?siteToken=kwQEuu6" charset="utf-8"></script>';
			$google_links_channel = ''; # Don't count bulletins
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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4538";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Blu-ray Review: Star Wars (Episodes I-III)" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Blu-ray Review: Star Wars (Episodes I-III)" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Blu-ray Review: Star Wars (Episodes I-III)" />
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
	<title>HDTV Magazine - Blu-ray Review: Star Wars (Episodes I-III)</title>
	<meta name="keywords" content="star wars, george lucas, audio commentary, ● audio, – stars, star, wars, ●, –, audio, stars, george, lucas, commentary, color, original, disc, interviews, ’s, cast, crew, minutes, apx, episode, movie" />
	<meta name="description" content="The May 25, 1977 theatrical debut of Star Wars - on a scant 32 screens across America - was destined to change the face of cinema forever. An instant classic and an unparalleled box office success, the rousing &quot;space opera&quot; was equal parts fairy tale, western, 1930s serial and special effects extravaganza, with roots in mythologies from cultures around the world. From the mind of visionary writer/director George Lucas, the epic space fantasy introduced the mystical Force into the cultural vocabulary, as well as iconic characters such as evil Darth Vader, idealistic Luke Skywalker, feisty Princess Leia, lovable scoundrel Han Solo and wise Obi-Wan Kenobi. Since its 1977 debut, Star Wars has continued to grow, its lush narrative expanding from modest beginnings into an epic, six-film Saga chronicling the fall and redemption of The Chosen One, Anakin Skywalker.

If the original trilogy never existed, and all we had were the prequels, I think they would be considered good movies. However, they will always live in the shadow of Luke, Leia, and Han. Episodes I – III lack the charisma and fun of the original trilogy, the plot is..." />
	<meta name="title" content="Blu-ray Review: Star Wars (Episodes I-III)" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Blu-ray Review: Star Wars (Episodes I-III)" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2011/10/bluray-review-star-wars-episodes-iiii.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="The May 25, 1977 theatrical debut of Star Wars - on a scant 32 screens across America - was destined to change the face of cinema forever. An instant classic and an unparalleled box office success, the rousing &quot;space opera&quot; was equal parts fairy tale, western, 1930s serial and special effects extravaganza, with roots in mythologies from cultures around the world. From the mind of visionary writer/director George Lucas, the epic space fantasy introduced the mystical Force into the cultural vocabulary, as well as iconic characters such as evil Darth Vader, idealistic Luke Skywalker, feisty Princess Leia, lovable scoundrel Han Solo and wise Obi-Wan Kenobi. Since its 1977 debut, Star Wars has continued to grow, its lush narrative expanding from modest beginnings into an epic, six-film Saga chronicling the fall and redemption of The Chosen One, Anakin Skywalker.

If the original trilogy never existed, and all we had were the prequels, I think they would be considered good movies. However, they will always live in the shadow of Luke, Leia, and Han. Episodes I – III lack the charisma and fun of the original trilogy, the plot is..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4538', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2011/10/bluray-review-star-wars-episodes-iiii.php">Blu-ray Review: Star Wars (Episodes I-III)</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>October  7, 2011</b>
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
				<h2>4.9 Stars (out of 5)</h2> <p>Synopsis</p> <p>The May 25, 1977 theatrical debut of Star Wars - on a scant 32 screens across America - was destined to change the face of cinema forever. An instant classic and an unparalleled box office success, the rousing "space opera" was equal parts fairy tale, western, 1930s serial and special effects extravaganza, with roots in mythologies from cultures around the world. From the mind of visionary writer/director George Lucas, the epic space fantasy introduced the mystical Force into the cultural vocabulary, as well as iconic characters such as evil Darth Vader, idealistic Luke Skywalker, feisty Princess Leia, lovable scoundrel Han Solo and wise Obi-Wan Kenobi. Since its 1977 debut, Star Wars has continued to grow, its lush narrative expanding from modest beginnings into an epic, six-film Saga chronicling the fall and redemption of The Chosen One, Anakin Skywalker.</p> <p>Starring:</p> <p>Ewan McGregor, Hayden Christensen, Natalie Portman, Ian McDiarmid, Samuel L. Jackson, Christopher Lee, Liam Neeson, Jake Lloyd, Ray Park, Anthony Daniels, Kenny Baker, Peter Mayhew, Keisha Castle-Hughes, Jimmy Smits, Ahmed Best</p> <p>Director:</p> <p>George Lucas</p> <p>Blu-ray Release Date:</p> <p>September, 16, 2011</p> <p>Subtitles:</p> <p>English SDH, French, Spanish, Portuguese</p> <p>Rating</p> <p>Overall rating weighted as follows:</p> <p><b>Audio 40%, Video 40%, Special Features 20%</b>, <b>Movie - its just our opinion so take it with a grain of salt</b></p> <h2>Audio 5.0 Stars (out of 5)</h2> <p><i>Dolby and DTS Demo Discs used as basis for comparison</i></p> <p>● Subwoofer – <b>5.0 Stars</b></p> <p>● Dialog – <b>5.0 Stars</b></p> <p>● Surround Effects – <b>5.0 Stars</b></p> <p>● Dynamic Range – <b>5.0 Stars</b></p><b></b> <p><b>English:</b> DTS-HD Master Audio 6.1, <b>Spanish:</b> Dolby Digital 5.1, <b>French:</b> DTS 5.1, <b>French:</b> Dolby Digital 5.1, <b>Portuguese: </b>Dolby Digital 5.1<b></b></p> <p>As soon as the famous John Williams score pumps through the speakers, it’s clear that Star Wars movies are what home theaters were made for. Without a doubt, this is the best audio performance I have ever heard on Blu-ray. The audio is so perfect; it made me enjoy watching the Phantom Menace. The pod race scene sounded amazing. In Attack of the Clones the battle on Geonosis has so much going on it’s almost too much. The final battles between Yoda verses the Emperor and Anakin verses Obi-Wan are filled with heavy rumbling bass that commands you to feel the force. Overall, every prequel features similar qualities. Deep sounding explosions fill the room, lasers wiz across the room with pinpoint accuracy, spaceships pan the room, and lightsabers resonate as the zip across all channels. Dialog is never lost in the mix.</p> <h2>Video 4.7 Stars (out of 5)</h2> <p><i>Spears &amp; Munsil Benchmark Blu-ray Edition used as basis for comparison</i></p> <p>● Color Accuracy - <b>4.5 Stars</b></p> <p>● Shadow detail – <b>4.0 Stars</b></p> <p>● Clarity – <b>5.0 Stars</b></p> <p>● Skin tones – <b>5.0 Stars</b></p> <p>● Compression – <b>5.0 Stars</b></p> <p><b>Video codec: </b>MPEG-4 AVC,, <b>Video resolution:</b> 1080p, <b>Aspect ratio: </b>2.35:1, <b>Original aspect ratio:</b> 2.39:1<b></b></p> <p>The Star Wars prequels have low film grain, and the color palette is warm and slightly vibrant. Detail is amazing. It’s crisp and clear enough to notice burlap sack patterns in clothing, marvel at the thousands of deep cutting wrinkles on Yoda’s old face, see every force filled whisker on Obi-Wan’s beard, and shutter at the vast emptiness in Jar-Jar Binks’ eyes. At times dark scenes can loose detail in the shadows, but show not signs of compression issues. It is interesting to note that when CGI characters and live actors are in the same scene, the fake character’s colors and shadows do not always match the rest of the screen. However, this is only noticeable in The Phantom Menace, and is forgivable since the movie is 12 years old and revolutionized the method of adding CGI characters to real scenery.</p> <h2>Bonus Features 5.0 Stars (out of 5)</h2> <p>DISC ONE – STAR WARS: EPISODE I THE PHANTOM MENACE</p> <p>● Audio Commentary with George Lucas, Rick McCallum, Ben Burtt, Rob Coleman, John Knoll, Dennis Muren and Scott Squires</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC TWO – STAR WARS: EPISODE II ATTACK OF THE CLONES</p> <p>● Audio Commentary with George Lucas, Rick McCallum, Ben Burtt, Rob Coleman, Pablo Helman, John Knoll and Ben Snow</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC THREE – STAR WARS: EPISODE III REVENGE OF THE SITH</p> <p>● Audio Commentary with George Lucas, Rick McCallum, Rob Coleman, John Knoll and Roger Guyett</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC FOUR – STAR WARS: EPISODE IV A NEW HOPE</p> <p>● Audio Commentary with George Lucas, Carrie Fisher, Ben Burtt and Dennis Muren</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC FIVE – STAR WARS: EPISODE V THE EMPIRE STRIKES BACK</p> <p>● Audio Commentary with George Lucas, Irvin Kershner, Carrie Fisher, Ben Burtt and Dennis Muren</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC SIX – STAR WARS: EPISODE VI RETURN OF THE JEDI</p> <p>● Audio Commentary with George Lucas, Carrie Fisher, Ben Burtt and Dennis Muren</p> <p>● Audio Commentary from Archival Interviews with Cast and Crew</p> <p>DISC SEVEN – NEW! STAR WARS ARCHIVES: EPISODES I-III</p> <p>● Including: deleted, extended and alternate scenes; prop, maquette and costume turnarounds; matte paintings and concept art; supplementary interviews with cast and crew; a flythrough of the Lucasfilm Archives and more</p> <p>DISC EIGHT – NEW! STAR WARS ARCHIVES: EPISODES IV-VI</p> <p>● Including: deleted, extended and alternate scenes; prop, maquette and costume turnarounds; matte paintings and concept art; supplementary interviews with cast and crew; and more</p> <p>DISC NINE – THE STAR WARS DOCUMENTARIES</p> <p>● Star Warriors (2007, Color, Apx. 84 Minutes) – Some Star Wars fans want to collect action figures...these fans want to be action figures! A tribute to the 501st Legion, a global organization of Star Wars costume enthusiasts, this insightful documentary shows how the super-fan club promotes interest in the films through charity and volunteer work at fundraisers and high-profile special events around the world.</p> <p>● A Conversation with the Masters: The Empire Strikes Back 30 Years Later (2010, Color, Apx. 25 Minutes) – George Lucas, Irvin Kershner, Lawrence Kasdan and John Williams look back on the making of The Empire Strikes Back in this in-depth retrospective from Lucasfilm created to help commemorate the 30th anniversary of the movie. The masters discuss and reminisce about one of the most beloved films of all time.</p> <p>● Star Wars Spoofs (2011, Color, Apx. 91 Minutes) – The farce is strong with this one! Enjoy a hilarious collection of Star Wars spoofs and parodies that have been created over the years, including outrageous clips from Family Guy, The Simpsons, How I Met Your Mother and more — and don’t miss “Weird Al” Yankovic’s one-of-a-kind music video tribute to The Phantom Menace!</p> <p>● The Making of Star Wars (1977, Color, Apx. 49 Minutes) – Learn the incredible behind-the-scenes story of how the original Star Wars movie was brought to the big screen in this fascinating documentary hosted by C-3PO and R2-D2. Includes interviews with George Lucas and appearances by Mark Hamill, Harrison Ford and Carrie Fisher.</p> <p>● The Empire Strikes Back: SPFX (1980, Color, Apx. 48 Minutes) – Learn the secrets of making movies in a galaxy far, far away. Hosted by Mark Hamill, this revealing documentary offers behind-the-scenes glimpses into the amazing special effects that transformed George Lucas’ vision for Star Wars and The Empire Strikes Back into reality!</p> <p>● Classic Creatures: Return of the Jedi (1983, Color, Apx. 48 Minutes) – Go behind the scenes — and into the costumes — as production footage from Return of the Jedi is interspersed with vintage monster movie clips in this in-depth exploration of the painstaking techniques utilized by George Lucas to create the classic creatures and characters seen in the film. Hosted and narrated by Carrie Fisher and Billie Dee Williams.</p> <p>● Anatomy of a Dewback (1997, Color, Apx. 26 Minutes) – See how some of the special effects in Star Wars became even more special two decades later! George Lucas explains and demonstrates how his team transformed the original dewback creatures from immovable rubber puppets (in the original 1977 release) to seemingly living, breathing creatures for the Star Wars 1997 Special Edition update.</p> <p>● Star Wars Tech (2007, Color, Apx. 46 Minutes) – Exploring the technical aspects of Star Wars vehicles, weapons and gadgetry, Star Wars Tech consults leading scientists in the fields of physics, prosthetics, lasers, engineering and astronomy to examine the plausibility of Star Wars technology based on science as we know it today.</p> <h2>Movie – 3.5 Stars (out of 5)</h2> <p>Review</p> <p>If the original trilogy never existed, and all we had were the prequels, I think they would be considered good movies. However, they will always live in the shadow of Luke, Leia, and Han. Episodes I – III lack the charisma and fun of the original trilogy, the plot is a very busy and it takes itself too seriously. Let’s face it; the biggest problem with the prequels is Anakin. He whines way too much, and Hayden Christensen’s performance isn’t the greatest. There’s also everyone’s favorite gripe, Jar-Jar Binks. Since C-3P0 and R2-D2 aren’t in Episode I much, Lucas tried to make a new character the comic relief, and made an annoying long-eared clown instead. However, there are a few reasons to like the prequels. The lightsaber battles are faster and there’s more than one per movie. The prequels look slick and rich, and the costume design for Princess Amidala is incredible. The action sequences are big, loud, and extravagant.</p> <p>There has been lots of controversy concerning the changes that George Lucas has made to this release of Star Wars. In these three episodes only one change is notable. In the original Phantom Menace Yoda was a puppet designed buy Frank Oz. In this Blu-ray version, Yoda is now completely CGI. I think this is actually an improvement. The change made Yoda consistent with the rest of the prequels, and I always thought the puppet version was creepy.</p> <p>If I’m in the mood to watch Star Wars I always watch the original trilogy. It’s the version I grew up with, and it’s like spending time with old friends. My kids on the other hand, have grown up with the prequels, and are confused by Harrison Ford being Han Solo and Indiana Jones. These days Anakin is more popular than Luke, and they have no understanding of how cool Billy Dee Williams is. They don’t look at Star Wars as the prequel and original trilogies; it’s just Star Wars and they like it. Maybe I could learn something from that, but until then I like Star Wars the best with a Death Star, a Millennium Falcon, and a bad guy with asthma problems.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>October  7, 2011  9:30 PM</b>
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
			<?=getComments(4538)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4538)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2011/10/bluray-review-star-wars-episodes-iiii.php" type="text/javascript" charset="utf-8"></script>
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