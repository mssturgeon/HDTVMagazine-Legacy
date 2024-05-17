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
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 4673 AND placement_is_primary = 1";
	if ($debug) echo "$sql\n";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get auxiliary information
	$sql = "SELECT t.topic_id, topic_replies, ASIN, pg_masterid, image_src
	FROM aux_mt_entry a, ". TOPICS_TABLE ." t
	WHERE a.entry_id = 4673
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
#	<li class="li_horizontal"><iframe src="http://www.facebook.com/plugins/like.php?href=http://www.hdtvmagazine.com/test/2012/02/is-ces-worth-attending-anymore.php&amp;layout=button_count&amp;show_faces=false&amp;width=100" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px" allowTransparency="true"></iframe></li>

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
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 4673";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Is CES Worth Attending Anymore?" height="15" width="85"/></a></span>';
			$meta_medium_type = 'audio';
			$link_rel_image_src = 'http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg';
			$meta = <<<EOT
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Is CES Worth Attending Anymore?" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="audio_src" href="$enclosure_url" />
	<meta property="og:audio" content="$enclosure_url" />
	<meta property="og:audio:title" content="Is CES Worth Attending Anymore?" />
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
	<title>HDTV Magazine - Is CES Worth Attending Anymore?</title>
	<meta name="keywords" content="new products, technology review, product may, business suits, real press, ces, press, may, industry, oled, people, image, products, get, even, product, been, see, technology, new, because, hdtv, every, front, real" />
	<meta name="description" content="For those that have been under a rock for the past few decades &lt;a href=&quot;http://www.cesweb.org/&quot;&gt;CES&lt;/a&gt; is the International Consumer Electronics Show that every year is held in Las Vegas in January. I have been attending it for about 15 years, my purpose? HDTV, but also hi-end audio.

After 6 days and 80+ meetings of product/exhibitor appointments the short answer is..." />
	<meta name="title" content="Is CES Worth Attending Anymore?" />
	<meta name="medium_type" content="<?=$meta_medium_type?>" />
	<link rel="image_src" href="<?=$link_rel_image_src?>" />

	<meta property="og:title" content="Is CES Worth Attending Anymore?" />
	<meta property="og:type" content="<?=$og_type?>" />
	<meta property="og:url" content="http://www.hdtvmagazine.com/test/2012/02/is-ces-worth-attending-anymore.php" />
	<meta property="og:image" content="<?=$link_rel_image_src?>" />
	<meta property="og:description" content="For those that have been under a rock for the past few decades &lt;a href=&quot;http://www.cesweb.org/&quot;&gt;CES&lt;/a&gt; is the International Consumer Electronics Show that every year is held in Las Vegas in January. I have been attending it for about 15 years, my purpose? HDTV, but also hi-end audio.

After 6 days and 80+ meetings of product/exhibitor appointments the short answer is..." />
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
					<a href="javascript:popOpen('/admin/asin-edit.php?entry_id=4673', 340, 125);">Link Products</a>
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
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/test/2012/02/is-ces-worth-attending-anymore.php">Is CES Worth Attending Anymore?</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>Shane Sturgeon</b> on <b>February  7, 2012</b>
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
				<p>For those that have been under a rock for the past few decades <a href="http://www.cesweb.org/">CES</a> is the International Consumer Electronics Show that every year is held in Las Vegas in January. I have been attending it for about 15 years, my purpose? HDTV, but also hi-end audio. <p>After 6 days and 80+ meetings of product/exhibitor appointments the short answer is: maybe for you not anymore for me, much less as a press attendee, and especially considering I have been footing the cost from my own pocket since the 90s to help the readers and the magazines I work with. <p>Do not get me wrong, CES was and still is a good opportunity to see in one event most of the new products and manufacturers of the HDTV industry under one (very large) roof without having to attend individual press invitations in various cities when companies announce/introduce their new products. <p>But quite frankly, CES does not allow anymore for adequate viewing of TV technologies. In order to get to view them plenty of walk and time needs to be invested. Comparative judgment becomes mostly subjective even when returning to the products several times to been able to intelligently respond to questions like: which of the two 55” OLED images was actually better and why? Often the experience feels as isolated as seeing the products in different events.  <p>During my 6 days I had to come back to repeat views of OLEDs, 4Ks, 8K, 3DTVs, etc. several times to evaluate their image objectively. In some cases it was until the very last day (2 press, 4 show) that I realized I may need to change my mind about some products I thought they were super on the first day, but on the 6th day I detected some image artifacts, or some softness, or over-saturation of colors, or image aberrations when displaying geometric objects and diagonals, or when displaying real objects of known color and shape, or when angling my view, from the bottom, from the side, or both, in a way I was not able to see on the first 4 or 5 days of repeated viewing because of the crowd or the chosen content, or because I entirely missed the observation. How would the white SONY logo displayed on an image appear to you as pink? It did, and that surprised me. <p>What was especially surprising to me was that products that appealed to me initially for their uniqueness and beauty, like OLED, were more revealing when I was able to isolate my mind and ears from the CES zoo and concentrate in what I actually see, ignoring the dozens of people crossing in front of me or the questions. <p>Yes, OLED looked attractive; who wants to hear that is over-saturated to the point of color bleeding over the edges of the flowers shown on an incredible black background? It may not have been noticeable to thousands of “expert” bloggers in the Internet, until someone opens the subject. <p>OLED offers a very striking image, perhaps too striking to be natural, an image that may be begging for ISF calibration to tone it down once the set gets home so one can watch it more than 5 minutes without visual fatigue. Frankly, I rather have an image that offers more extension in contrast, brightness and color, because hopefully I may be able to calibrate the set down to my taste (and for my eyes not to bleed), rather than been stuck with the reverse scenario.  <p><b>HDTV Technology Review </b> <p>As you may be aware between 2002 and 2007 I produced an annual <a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">HDTV Technology Review</a> for magazine readers, it started to be 100+ pages released about 3 months after each CES, but by 2007 it grew to 560 pages and 9 months of work (the book offered by Display Search as an <a href="http://www.displaysearch.com/cps/rde/xchg/SID-0A424DE8-F7544E93/displaysearch/hs.xsl/pr_242.asp">industry edition</a>). <p>I stop doing that because it took too much effort to produce by a single person, and because the HDTV industry moved faster than what I reflected in my content and its intellectual value shifted from the original purpose, now that the majority of households have an HDTV. <p>The part of the content dedicated to technology, standards, DTV implementation, satellite/cable/over-the-air/IPTV, and industry advances still maintained an historical value, but not the major effort in detailing features, specs and reviews of hundreds of HDTVs, DVRs, video processors, and TV hardware in general, their shelf life is very short. <p><b>CES People</b> <p>CES with 153K (unconfirmed) people was no different than the 140K+ people of previous years in that it is an overcrowded event that cannot allow me to perform my objective efficiently, much less as press attendee, with lots of people cutting in front of every step you make, pushing you around, or stopping/turning suddenly blocking the walking areas. One may think that walking miles is a healthy exercise, it is, but the constant push and pull to get to any exhibitor made the interrupted walking disturbing and stressful. <p>This year it was noticeable the abundance of people that were unrelated to the TV industry blocking the view of a TV demo, a demo the real industry related people/press needed to evaluate. While observing a set I was interrupted many times by others asking questions that made obvious their disassociation with the industry or product in front of them (what is an OLED? should I wear the 3D glasses?), and their curiosity became rather obstructive to my objective. <p>In my case OLED, 4K, 3D 4K, and 8K were among the subjects of interest for me, I came to CES to evaluate new efforts and improvements in 2D and 3D image quality, but when trying to review a technology or product to form a judgment about image quality, it was like a competition to find a spot to cleanly see a product without people constantly crossing in front and pushing each other around, not to mention if I tried to take a clear photo of anything. Did you ever try to get a good spot on the habitat window of the just born baby Panda in the zoo, the other zoo, on Sunday? <p>In addition CES has expanded the definition of actual Press to apparently allow almost anyone that may write a paragraph blog about CE in their personal website. According to CEA, 5000 media and analysts attended; if they were, 90% appeared to be bloggers, and 10% real press professionals that know well the technology they work with. <p>The abundance of writers may certainly help CES publicly, but makes very difficult my job. I also observed that most come to the show wearing shorts, jeans with holes, T-shirts, dirty tennis shoes, unshaved, apparently unshowered, wearing baseball hats to meetings with people with business attires, you name it. <p>They cut in lines in front of an actual press person in business suits that was waiting for an hour (me) like no one would notice or mind. They look like they are just an organic extension on their keyboard and cell phone, isolated from reality and probably writing about a product they may have not even seen in the floor but read about in the other blogs, like many do from their kitchen chair without even going to CES. <p>They add their own single paragraph at the end of copy-and-pasted text obtained from similar Internet luminaries, but using the opportunity to express their opinion based on their twisted interpretation of facts (if they research facts) because "their readers might be waiting and eager to read about the blogger’s professional review".&nbsp; <p>Meanwhile, the real press, the ones that treat their jobs professionally and in business suits, the ones that actually need (and know how to properly) review the products and technologies, cannot even get closer to them due to industry unrelated bystanders and bloggers. Although I noticed an improvement at the exhibits: we can now communicate in English in many demos, and, surprisingly, we can get a response in English as well; in the US! This is progress!!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <p>Although press day may appear to be best chance to see any breakthrough technology or product before the zoo arrives, it is actually deceiving. Sometimes it could happen if one can wait 1-hour-lines and cancel the before/following press conference meetings.&nbsp; <p>So one better guess right which manufacturer (LG, or Samsung, or Panasonic, etc.) may announce a breakthrough product that one may not want to miss. Should LG’s OLED be given priority and miss Samsung’s OLED presentation? Or should one go to a (growing trend of) other hotel exhibitions (like OLED by LG Display) and miss a couple of hours of the other valuable press presentations? (Have you even tried to move around in Vegas?) About missing Sharp’s new pixel adventure, or their new TV as big as a small car? <p>Having a VIP pass for shorter lines was not the answer either for me. I greeted familiar faces of “actual” press colleagues of main publications waiting in the same VIP line, all of us complaining about the same struggle.&nbsp; And even then I had to miss part/whole meetings due to a tight press conference schedule that does not have consideration for the excessively long waiting lines.&nbsp; <p>So what is the solution? Skip CES and attend CEDIA with about 1/6 of the crowd? CEDIA is held a few months before CES and is dedicated to the Home Theater industry. There one can usually see many new products CES will show a few months later, like Sony’s and JVC’s 4K projectors in CEDIA 2011, although the OLEDs and the 4K 3D passive 84” LG LED panel were not shown there. But who cares, we can all get the expert reviews from the kitchen bloggers typing in their pajamas. <p>I have been saying this to myself every year, but it feels this year CES gave me the strength to reach a decision point of ignoring it next year, unless CES finds a way to separate the crowds and exhibitors of major industries, such as audio and video areas not allowing unrelated attendees, such robotics, computers, photo, phones, car related products, etc. <p>In other words CES could still have 153K people but by grouping the attendance and related press (even on press day) to only the corresponding industry the event may maximize the productivity of professionals of every industry and still get the coverage CES wants, the benefit would also be that press coverage of every industry would be made by the real experts on their domain, rather than a blogger of cell phones writing about 4K and OLED. <p><b>Image Evaluations</b> <p>Now, do you really want to know my evaluation of Crystal LED from Sony, OLED from LG and Samsung, the 8K panel from Sharp, the 4K passive 3D 84” panel from LG, Toshiba’s and others Glasses-free 3D? Keep yourself tuned.</p>
				<?=$pg_mlink?>
				<p class="posted">
					Posted by <b>Shane Sturgeon</b>, <b>February  7, 2012  8:42 PM</b>
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
			<?=getComments(4673)?>
			<div class="dottedline"></div>

			<? if (6 != 7) echo getBoxMoreFromAuthor('Shane Sturgeon', 4673)?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/test/2012/02/is-ces-worth-attending-anymore.php" type="text/javascript" charset="utf-8"></script>
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