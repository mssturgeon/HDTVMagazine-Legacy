<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 403";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 403 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, sanyo xacti, still images, memory card, digital media, video, sanyo, SANYO, definition, high, mode, digital, still, camera, recording, Xacti, xacti, images, media, new, card, features, memory, shooting, lcd" />
	<meta name="description" content="A video recording mode optimized for the video iPod&amp;reg;, 16:9 widescreen still picture mode and easy, convenient in-camera video editing. The SANYO Xacti HD1a features an ergonomic, one-handed operation. It can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. in September 2006 at a very competitive $699.99** MSRP." />
	<title>HDTV Magazine Bulletins - New Sanyo High-Definition Digital Media Camera</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_sanyo_high-definition_digital_media_camera';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('New Sanyo High-Definition Digital Media Camera'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">New Sanyo High-Definition Digital Media Camera</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>July 25, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php&amp;phase=2&amp;title=New%20Sanyo%20High-Definition%20Digital%20Media%20Camera&amp;bodytext=A%20video%20recording%20mode%20optimized%20for%20the%20video%20iPod%26reg%3B%2C%2016%3A9%20widescreen%20still%20picture%20mode%20and%20easy%2C%20convenient%20in-camera%20video%20editing.%20The%20SANYO%20Xacti%20HD1a%20features%20an%20ergonomic%2C%20one-handed%20operation.%20It%20can%20record%20both%20720p%20high-definition%20video%20and%205.1%20megapixel%20digital%20still%20images%20to%20a%20standard%20SD%20flash%20memory%20card.%20It%20will%20be%20available%20in%20the%20U.S.%20in%20September%202006%20at%20a%20very%20competitive%20%24699.99%2A%2A%20MSRP.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">This press release is actually over a week old, but due to the website difficulties we were experiencing at the time, it was never published. Hopefully it is still news to some of you, although it is not necessarily a "bulletin" anymore. &raquo; Shane</p>

<center><b>New SANYO High-Definition Digital Media Camera Features Optimized Recording for Portable Video Players and 16:9 Still Mode Ideal for Widescreen Viewing</b></center>

<center><i>SANYO Xacti HD1a Offers In-Camera Editing, 2.2-inch LCD Display and $699.99 MSRP</i></center>

<p><img src="/images/bulletins/Xacti-HD1a.jpg" alt="Xacti HD1a" align="left">CHATSWORTH, Calif., July 13 /PRNewswire-FirstCall/ -- A video recording mode optimized for the video iPod&reg; and other portable video players tops the advanced feature set of the new SANYO Xacti HD1a high-definition compact digital media camera. Following quickly on the strong sales and critical success of the SANYO Xacti HD1 introduced in January 2006, the new high-definition HD1a also adds such compelling features as a selectable 16:9 widescreen still picture mode and easy, convenient in-camera video editing.</p>

<p>The SANYO Xacti HD1a retains its title as the world's smallest and lightest high-definition digital media camera*. Featuring ergonomic, one-handed operation, the camera can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. from SANYO, the world's leading manufacturer of digital cameras and components, in September 2006 at a very competitive $699.99** MSRP.</p>

<p>"The remarkable success of our precedent-setting Xacti HD1, along with user feedback, prompted SANYO to incorporate several new key features into the new HD1a," said John Lamb, Senior Marketing Manager, for SANYO Fisher Company's Audio Video Division. "Our inclusion of a 30 frames-per-second 320 x 240 pixel video recording mode -- optimized for viewing MPEG-4 video clips on a video iPod or other portable video player -- reflects a shift by consumers towards on-the-go viewing."</p>

<p><b>Video recording optimized for personal media players</b><br />
The HD1a's new "Web-SHQ" recording mode is designed specifically to capture video destined for the video iPod and other popular MPEG-4 capable personal media players. For optimal playback on such devices, video is captured at a resolution of 320 x 240 pixels and a smooth and natural 30 frames per second.</p>

<p><b>16:9 still shooting</b><br />
An all-new 16:9 shooting mode allows users to capture 3.8 megapixel stills in the same widescreen format as their high-definition videos for eye-catching viewing on a 16:9 television screen.</p>

<p><b>Convenient in-camera video editing</b><br />
The HD1a features enhanced video editing functions, enabling quick A>B deletions and easy combining of video clips. In-camera editing makes it easy to remove unwanted material and helps conserve memory card space.</p>

<p><b>High-definition engine</b><br />
The SANYO Xacti HD1a is powered by high-precision LSI (large-scale integration) circuitry for advanced, high-definition image processing. This powerful "high-definition engine" quickly executes a vast number of calculations and enables the HD1a to realize image processing functions such as high-definition 720p processing, real-time MPEG-4 compression and noise reduction.</p>

<p><b>2.2-inch LCD display</b><br />
The HD1a features a large Sanyo-developed 2.2-inch LCD display with 210,000 total pixels for exceptional viewability. The display flips out from the camera and rotates up to 285 degrees on axis for taking great video or still images even in difficult locations.</p>

<p><b>10x optical zoom</b><br />
The HD1a features a generous and highly efficient 10x optical zoom lens. Built from 12 elements designed in 9 groups and with a built-in neutral density filter, the 10x zoom lens has a maximum aperture of f/3.5 in both wide and telephoto angles, allowing for clear images in lower light situations. Combined with a 10x digital zoom capability, the HD1a is capable of a total 100x zoom.</p>

<p><b>Digital video and stills, all in one</b><br />
Like the Xacti HD1 and all previous SANYO Xacti digital media cameras, the HD1a can record still images in addition to video clips. Utilizing the 5.36 megapixel (total) CCD, the HD1a captures beautiful 5.1 megapixel still images which are recorded directly onto a standard SD memory card. The camera can record both 5.1 megapixel still images and high-definition (1280 x 720-pixel) digital video at the same time with a simple press of the shutter button during the shooting of a video clip.***</p>

<p><b>21 minutes of HD video per Gigabyte of memory</b><br />
The HD1a can record up to 21 minutes of 720p HD video per Gigabyte on a standard SD or SDHC memory card. That's up to 42 minutes on a 2-Gigabyte card or 84 minutes on the soon to be released 4-Gigabyte SDHC card (cards sold separately). Alternatively, HD1 users can select to record in Standard Definition mode (640 x 480 pixels at 30fps progressive) for up to one hour per Gigabyte of recording capacity. Users can quickly switch between high-definition and standard-definition recording modes by simply pressing the "HD/Norm" button located beneath the LCD display.</p>

<p><b>Ergonomic and lightweight</b><br />
Designed for convenient, one-handed operation and one-thumb control of most key functions, the truly ergonomic SANYO HD1a is ready to use whenever inspiration strikes, at home or away. Lightweight at only 8.3 ounces (including battery and a standard SD memory card), the HD1a measures 3.1" W x 4.7" H x 1.4" D.</p>

<p><b>Key SANYO Xacti HD1a features include:</b><br />
- 320 x 240 "Web-SHQ" video mode; optimal for playback on many personal media players<br />
- 16:9 widescreen MPEG-4 video (HD-SHQ / HD-HQ modes)<br />
- 16:9 widescreen digital still mode for stunning widescreen TV playback<br />
- Highly efficient 10x optical zoom<br />
- 2.2" rotating LCD display<br />
- CD-quality AAC-LC (MPEG-4 Audio) stereo recording<br />
- Enhanced video editing functions for quick A>B deletions and easy combining of clips<br />
- 60 fps Fluid Motion Recording (640 x 480 TV-HR Mode)<br />
- Rapid 5.1 megapixel sequential still shooting<br />
- Pop-up flash with double the brightness of conventional models<br />
- Anti-shake digital image stabilizer<br />
- Talking navigation guide for first-time users<br />
- Super-fast 1.7-second camera startup<br />
- Versatile manual mode enables advanced-control shooting<br />
- Super Macro shooting down to 1 cm (W) / 1 m (T)<br />
- Self timer (2 seconds / 10 seconds)<br />
- Voice recorder function: over 33 hours recording time with optional 2 GB SD Memory Card<br />
- Red-eye reduction mode<br />
- Multifunction docking station<br />
- High-capacity SANYO rechargeable Lithium-ion battery<br />
- Remote control included<br />
- Exif Print and Print Image Matching III<br />
- PictBridge-capable for PC-Free printing with PictBridge-compatible printers</p>

<p>SANYO Electric Co., Ltd. (Nasdaq: SANYY) is a $23 billion manufacturer and distributor of consumer and commercial electronics, including multimedia and telecommunication products. Based in Chatsworth, California, SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.) markets digital cameras, PCS phones, audio systems, portable and mobile electronics, televisions, DVD players, dictation devices, home appliances, LCD projectors, security video equipment and air conditioning systems.</p>

<p>For more information and additional specifications, please visit <a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyodigital.com/">www.sanyodigital.com</a>. Visit <a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyodigital.com/">www.sanyodigital.com</a> and click on "HD1a" > "Dealer Images" for downloadable hi-res product images.</p>

<center><i>###</i></center>
<i>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</i>

<p>*   Among commercially available high-definition media cameras, as of July 13, 2006.<br />
**  Estimated Selling Price: Actual prices set by dealer are subject to change.<br />
*** Depending on the mode used to take still images, simultaneous video clip shooting may be interrupted.</p>

<p><u><b>Sanyo Press Release:</b></u><br />
<a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyo.com/aboutsanyo/press_releases_detail.cfm?id=171">http://www.sanyo.com/aboutsanyo/press_releases_detail.cfm?id=171</a></p>

<p><u><b>HD1a Product Page:</b></u><br />
<a href="/cgi-bin/ntlinktrack.cgi?http://www.sanyodigital.com/video_cameras/HD1A/index.html">http://www.sanyodigital.com/video_cameras/HD1A/index.html</a></p>

<p><u><b>Editorial Contact:</b></u><br />
Michael R. Harris, Harris Public Relations<br />
for SANYO Fisher Company<br />
Tel: (714)966-0258, E-mail: hpr1@earthlink.net</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>July 25, 2006 04:26 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 403
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
 				AND entry_id <> 403
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
