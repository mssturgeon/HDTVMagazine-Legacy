<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 733";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 733 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="high definition, sanyo xacti, memory card, pocket sized, sanyo fisher, sanyo, SANYO, video, high, xacti, Xacti, easy, digital, camcorder, photos, still, movies, camera, new, images, definition, image, design, easily, card" />
	<meta name="description" content="SANYO, a world leading digital camera manufacturer, debuts the Xacti HD700, a pocket-sized, 720p high-definition digital camcorder. The HD700 is a high-performance camcorder capable of recording both high-definition video and 7.1-megapixel photos easily to an SD or SDHC memory card (memory card sold separately). Featuring an ultra-compact, ergonomic shape, the HD700 is specifically designed for publishing high quality video and high resolution still photos in formats ideally suited for Web-use or sharing. Fun and easy to use anytime inspiration strikes, the HD700 is truly an advanced communication tool for the emerging information age.

The SANYO Xacti HD700 will be available in" />
	<title>HDTV Magazine Bulletins - SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/09/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September 26, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/09/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/09/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder.php&amp;phase=2&amp;title=SANYO%20Introduces%20the%20Xacti%20HD700%2C%20a%20Pocket-Sized%20720p%20High-Defintion%20Camcorder&amp;bodytext=SANYO%2C%20a%20world%20leading%20digital%20camera%20manufacturer%2C%20debuts%20the%20Xacti%20HD700%2C%20a%20pocket-sized%2C%20720p%20high-definition%20digital%20camcorder.%20The%20HD700%20is%20a%20high-performance%20camcorder%20capable%20of%20recording%20both%20high-definition%20video%20and%207.1-megapixel%20photos%20easily%20to%20an%20SD%20or%20SDHC%20memory%20card%20%28memory%20card%20sold%20separately%29.%20Featuring%20an%20ultra-compact%2C%20ergonomic%20shape%2C%20the%20HD700%20is%20specifically%20designed%20for%20publishing%20high%20quality%20video%20and%20high%20resolution%20still%20photos%20in%20formats%20ideally%20suited%20for%20Web-use%20or%20sharing.%20Fun%20and%20easy%20to%20use%20anytime%20inspiration%20strikes%2C%20the%20HD700%20is%20truly%20an%20advanced%20communication%20tool%20for%20the%20emerging%20information%20age.%0A%0AThe%20SANYO%20Xacti%20HD700%20will%20be%20available%20in&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p class="prtitle">SANYO Introduces the Xacti HD700, a Pocket-Sized 720p High-Defintion Camcorder Designed for Fun, Everyday Use</p>

<center><i>Stylish, Easy-To-Use Camcorder Shoots 720p Video and 7.1 Megapixel Photos</i></center><br />
<br />

<p><img src="/images/products/sanyo-xacti-hd700.jpg" alt="Sanyo Xacti HD700" align="left" /><B>CHATSWORTH, Calif., Sept. 26 /PRNewswire-FirstCall/</B> -- SANYO, a world leading digital camera manufacturer, debuts the Xacti HD700, a pocket-sized, 720p high-definition digital camcorder. The HD700 is a high-performance camcorder capable of recording both high-definition video and 7.1-megapixel photos easily to an SD or SDHC memory card (memory card sold separately). Featuring an ultra-compact, ergonomic shape, the HD700 is specifically designed for publishing high quality video and high resolution still photos in formats ideally suited for Web-use or sharing. Fun and easy to use anytime inspiration strikes, the HD700 is truly an advanced communication tool for the emerging information age.</p>

<p>The SANYO Xacti HD700 will be available in Silver, Red and a gorgeous Brown color in the U.S.A. at the end of October, 2007, and has an MSRP of $599.99(1).</p>

<p>"The new SANYO Xacti HD700 is our best everyday-use camcorder ever," said John Lamb, SANYO's Senior Marketing Manager for the Xacti line. "At 16% smaller and 10% lighter than our popular HD2, the HD700 combines a host of new features in a sleek, affordable and easy-to-use camcorder."</p>

<p><br />
<B>HIGH-QUALITY VIDEO, EASY WEB USE</B></p>

<p>The HD700 uses the same new optical processing engine found in the recently announced VPC-HD1000, which is unique in its ability to process images quickly with lower power consumption. In addition to utilizing SANYO's proprietary H.264 engine, the camera only needs 4.0 watts of power, made possible by combining two engine chips into one miniaturized fast-processing image engine.</p>

<p>Video is recorded in the advanced MPEG-4 AVC/H.264 format encoding technology. This advanced compression delivers exceptional video clarity and detail while maintaining the smallest file size possible.</p>

<p>Also, in addition to the reduction in data capacity needed to store the images and video clips, photos or movies are easily copied and loaded on a personal computer or uploaded and shared on the Internet for online use on blogs, video-sharing websites and personal websites.</p>

<p><br />
<B>7.1-MEGAPIXEL STILL IMAGES</B></p>

<p>The HD700 captures 7.1-megapixel still photos and provides excellent low-light performance all the way to the ISO 3200 level of sensitivity.</p>

<p><br />
<B>AUTOMATIC "FACE CHASER" FUNCTION FOR STILL PHOTOS</B></p>

<p>The HD700 includes the new "Face Chaser" function that automatically detects and isolates faces to assist the camera's exposure and auto-focus. The HD700 is capable of detecting up to 12 independent faces at a time.</p>

<p><br />
<B>5X OPTICAL ZOOM</B></p>

<p>The HD700 digital media camera features a 5x all-glass optical zoom with a bright maximum aperture of f/3.5. Consisting of nine groups and twelve total lenses (3 aspheric elements, 5 aspheric surfaces), the HD700's lens provides a fantastic field-of-view with a 38-190 mm range (35 mm equivalent). Combined with the 12x digital zoom, the HD700 provides up to 60x zooming capability.</p>

<p><br />
<B>LARGE 2.7 INCH WIDESCREEN DISPLAY</B></p>

<p>The Xacti HD700 features a large 2.7 inch Liquid Crystal Display (LCD). The bright, widescreen display flips out from the camera and rotates up to 285 degrees on an axis that allows you to take great video or still images from otherwise-difficult-to-view positions, especially useful when shooting in large crowds or in small rooms.</p>

<p><br />
<B>MAC OS COMPATIBLE</B></p>

<p>The HD700 is designed to work with Apple's full complement of video editing applications including the new iMovie 08. Easily edit your movies and add them to your website or YouTube for sharing or create versions for iPod, iPhone or the Apple TV.</p>

<p><br />
<B>ADOBE(R) PREMIERE(R) ELEMENTS 3.0 INCLUDED</B></p>

<p>For Windows users, the SANYO Xacti HD700 includes the powerful award-winning Adobe Premiere Elements 3.0 video editing software. Adobe Premiere Elements 3.0 software makes creating and sharing impressive home videos a snap. Burn your footage to DVD in two simple steps, complete with a DVD menu and scene index, or easily assemble your movie by rearranging clips with drag-and-drop simplicity. And share your movies on DVD, the web, mobile phones, and virtually anywhere else.</p>

<p><br />
<B>CONVENIENT SD/SDHC MEMORY CARD STORAGE</B></p>

<p>The SANYO Xacti HD700 records high-definition and photos directly to a standard SD or SDHC Memory Card. The camcorder can record up to 2 hour and 46 minutes of 720p high-definition video on a single 8GB card (sold separately).</p>

<p><br />
<B>RANDOM ACCESS</B></p>

<p>Each video is recorded as an individual MPEG-4 and each still as a JPEG so you can have true random access allowing you to review a specific image or video quickly and easily, without waiting for tape rewinding or fast forwarding.</p>

<p><br />
<B>ERGOMOMIC DESIGN</B></p>

<p>The Xacti series has become known for its ergonomic design and small, easy-to-use file sizes. The HD700 is also designed for convenient, one-handed use and adopts the new 105 degree angle design that recent research has proven to be less tiring to hold and shoot than typical camcorders. Easy-to-hold and easy-to-shoot, the HD700 raises the bar in compact camcorder design.</p>

<p><br />
<B>ADDITIONAL SANYO XACTI HD700 FEATURES:</B></p>

<p> -- HDMI High-Definition Output (with included Docking Station)<br />
 -- Digital Image Stabilization<br />
 -- Superfast Startup (Record in as little as 1.3 seconds).<br />
 -- Playback directly onto a HD or standard TV screen.<br />
 -- Easy connection to VCR or DVD recorder.<br />
 -- Expand the fun of viewing home movies and photos on television with<br />
 'Xacti Library' for easy playback and saving of files.<br />
 -- Equipped with 'SIMPLE' mode for even beginners to create high quality,<br />
 beautiful high definition movies.<br />
 -- 'Talking Navigation' informs user of current state/setting<br />
 -- 'Super Macro' allows close-ups of 1 cm<br />
 -- In-Camera Editing<br />
 -- Equipped with 'Wind Noise Reduction' reducing wind noise from the<br />
 microphone when recording movies<br />
 -- Compatible with Exif Print(2) and PRINT Image Matching III(3) for<br />
 printing<br />
 -- Print pictures without a computer using PictBridge<br />
 -- Innovative Docking Station Included<br />
 -- Remote Control Included</p>

<p><br />
<B>About SANYO</B></p>

<p>SANYO Electric Co., Ltd. is a multi-billion-dollar global leader in providing solutions for the environment, energy and for lifestyle applications based on its Brand Vision 'Think GAIA'. SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.), based in Chatsworth, California, markets mobile phones, digital projectors, digital still cameras, digital media camcorders, home appliances, security video equipment, audio systems, portable and mobile electronics and HD televisions.</p>

<p>For more information and additional specifications, please visit http://www.sanyodigital.com/. For downloadable hi-res product images, go to http://www.sanyodigital.com/ and click on "Dealer Images".</p>

<p>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</p>

<p> (1) Manufacturer's Suggested Retail Price. Pricing subject to change at<br />
 any time. Actual prices are determined by individual dealers and may<br />
 vary.<br />
 (2) Exif Ver2.2 is an industry standard established for file formatting of<br />
 digital cameras<br />
 (3) When using PRINT Image Matching compatible printer</p>

<p>Source: SANYO Fisher Company</p>

<p>CONTACT: Michael R. Harris of Harris Public Relations, +1-805-418-7198,<br />
hpr1@earthlink.net, for SANYO Fisher Company</p>

<p>Web site: http://www.sanyo.com/</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September 26, 2007 05:35 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 733
 				AND a.topic_id = t.topic_id
 				AND t.topic_id = p.topic_id
 				AND p.post_id = pt.post_id
 			ORDER BY post_time";
 			$result = mQuery($sql);
 			$num_comments = mysql_num_rows($result);
 			
 			if ($num_comments > 0) {
 				# Skip the first one, as it's just the excerpt post.
 				$row = mysql_fetch_assoc($result);
 				$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id];
 				echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
 				'<div class="item"><span class="corners-top"><span></span></span>'.
 					'<img src="/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
 					'<a href="'. $thread_url .'">'. $row[topic_title] .'</a> <span class="grey">('. $row[topic_replies] .' replies)</span>'.
 				'<span class="corners-bottom"><span></span></span></div>';
 				
 				$x = 0;
 				while ($row = mysql_fetch_assoc($result)) {
 					if ($x == 10) break;
 					$x++;
 					$comment_url = URL_FORUM_VIEWTOPIC .'?p='. $row[post_id] .'#'. $row[post_id];
 					$text = strip_tags(str_replace('[', '<', str_replace(']', '>', $row[post_text])));
 					if ($row[post_subject] != '') {
 						$subject = $row[post_subject];
 					} else {
 						$subject = "Re: $row[topic_title]";
 					}
 	
 					$class = ($x % 2 == 0) ? 'item' : 'item_odd';
 					echo '<div class="'. $class .'"><span class="corners-top"><span></span></span>'.
 						'<div style="font-size:1.2em; font-weight:bold"><a href="'. $comment_url .'">'. $subject .'</a></div>'.
 						'<b>'. $row[poster_id] .'</b> '. date('M j, g:ia', $row[dt]) .'<br />'.
 						$text .
 					'<span class="corners-bottom"><span></span></span></div>';
 				}
 			}
 			if ($num_comments > $x) {
 				echo '<div align="center" class="important"><span class="corners-top"><span></span></span>'.
 				"Showing only excerpts from $x out of $num_comments, <a href='$thread_url'>Read More</a>".
 				'<span class="corners-bottom"><span></span></span></div>';
 			}
 		?><div class="dottedline"></div></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);
 	
 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 733
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);
 			
 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
 				mysql_data_seek($result, 0);
 				while ($row = mysql_fetch_assoc($result)) {
 					# Get categories
 					$sql = "
 					SELECT category_label FROM mt_category c, mt_placement p
 					WHERE $row[entry_id] = p.placement_entry_id
 						AND c.category_id = p.placement_category_id";
 					$res_categories = mQuery($sql);
 					$row_categories = mysql_fetch_assoc($res_categories);
 					$category = $row_categories[category_label];

 					$ts = strtotime($row[entry_created_on]);
 					$y = date('Y', $ts);
 					$m = date('m', $ts);
 					$blog_dir = getBlogDir($row[entry_blog_id]);
 					$date = getDateString($ts);
 					$link = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
 					echo '<li><a href="'. $link .'">'. $row[entry_title] .'</a> - <span class="grey">'. $category .'</span> - '. $date .'</li>';
 				}
 				echo '</ul><span class="corners-bottom"><span></span></span></div>';
				}
			}

 		if ($author[bio_short] != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p
 				WHERE
 					t.forum_id NOT IN (". EXCLUDE_FORUMS .")
 					AND p.poster_id = u.user_id
 					AND t.topic_id = p.topic_id
 					AND t.topic_last_post_id = p.post_id
 				ORDER BY post_time DESC LIMIT 10";
 				$result = mQuery($qry);
 				
 				while ($row = mysql_fetch_assoc($result)) {
   					$last_post = date('n/j g:ia T', $row[post_time]);
   					$title = html_entity_decode($row[topic_title]);
 		  					
 					echo '<li><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id] .'">'. $title .'</a> - <span class="grey">'. $row[post_author] .'</span> - '. $last_post .'</li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Authors</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT author_id, author_name, COUNT(*) num
 				FROM mt_author a, mt_entry e
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/sanyo_introduces_the_xacti_hd700_a_pocket-sized_720p_high-defintion_camcorder.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
