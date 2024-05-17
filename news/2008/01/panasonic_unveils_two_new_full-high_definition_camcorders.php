<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 870";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 870 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, intelligent shooting, shooting guide, sdhc memory, memory card, panasonic, Panasonic, high, High, definition, shooting, Definition, video, hdc, new, HDC, camcorder, card, Shooting, recording, memory, Memory, SDHC, Intelligent, Card" />
	<meta name="description" content="Panasonic, a leader in High Definition and digital video technologies, today announces two new full-High Definition 3CCD camcorders, the HDC-HS9, a hybrid model that can record to either an SD (or SDHC) Memory Card or its built-in 60-GB hard disk and the HDC-SD9, the world's smallest and lightest* SD card High Definition camcorder, weighing in at just 0.606 pounds. Both the SD9 and HS9, which feature new Panasonic camcorder technologies, Face Detection and Intelligent Shooting Guide, will be displayed at the 2008 International CES in Las Vegas from January 7-10 at the Panasonic booth #9405.

The Panasonic HDC-SD9 and HDC-HS9 offer..." />
	<title>HDTV Magazine Bulletins - Panasonic Unveils Two New Full-High Definition Camcorders</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_unveils_two_new_full-high_definition_camcorders';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic Unveils Two New Full-High Definition Camcorders'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/panasonic_unveils_two_new_full-high_definition_camcorders.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic Unveils Two New Full-High Definition Camcorders</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  6, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/panasonic_unveils_two_new_full-high_definition_camcorders.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/panasonic_unveils_two_new_full-high_definition_camcorders.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/panasonic_unveils_two_new_full-high_definition_camcorders.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/panasonic_unveils_two_new_full-high_definition_camcorders.php&amp;phase=2&amp;title=Panasonic%20Unveils%20Two%20New%20Full-High%20Definition%20Camcorders&amp;bodytext=Panasonic%2C%20a%20leader%20in%20High%20Definition%20and%20digital%20video%20technologies%2C%20today%20announces%20two%20new%20full-High%20Definition%203CCD%20camcorders%2C%20the%20HDC-HS9%2C%20a%20hybrid%20model%20that%20can%20record%20to%20either%20an%20SD%20%28or%20SDHC%29%20Memory%20Card%20or%20its%20built-in%2060-GB%20hard%20disk%20and%20the%20HDC-SD9%2C%20the%20world%27s%20smallest%20and%20lightest%2A%20SD%20card%20High%20Definition%20camcorder%2C%20weighing%20in%20at%20just%200.606%20pounds.%20Both%20the%20SD9%20and%20HS9%2C%20which%20feature%20new%20Panasonic%20camcorder%20technologies%2C%20Face%20Detection%20and%20Intelligent%20Shooting%20Guide%2C%20will%20be%20displayed%20at%20the%202008%20International%20CES%20in%20Las%20Vegas%20from%20January%207-10%20at%20the%20Panasonic%20booth%20%239405.%0A%0AThe%20Panasonic%20HDC-SD9%20and%20HDC-HS9%20offer...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Panasonic Unveils Two New Full-High Definition Camcorders, with New Face Detection and Intelligent Shooting Guide</p>

<center><i>World's Smallest and Lightest* SD Card HD Camcorder Makes Debut at CES 2008</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 6 /PRNewswire/</B> -- Panasonic, a leader in High Definition and digital video technologies, today announces two new full-High Definition 3CCD camcorders, the HDC-HS9, a hybrid model that can record to either an SD (or SDHC) Memory Card or its built-in 60-GB hard disk and the HDC-SD9, the world's smallest and lightest* SD card High Definition camcorder, weighing in at just 0.606 pounds. Both the SD9 and HS9, which feature new Panasonic camcorder technologies, Face Detection and Intelligent Shooting Guide, will be displayed at the 2008 International CES in Las Vegas from January 7-10 at the Panasonic booth #9405.</p>

<p>The Panasonic HDC-SD9 and HDC-HS9 offer full-High Definition**, 1920 x 1080 progressive recording (24 frames per second)*** and use the AVCHD video format. In addition, the new models feature Panasonic's original 3CCD system, Advanced MEGA O.I.S. (Optical Image Stabilizer), and a Leica Dicomar lens - three components that also contribute to the high-quality video output. Advanced MEGA O.I.S. checks and compensates for hand-shake around 4,000 times per second, about eight times more effectively than Panasonic's previous systems. In a 3CCD camera system, the light received through the lens is split into its three primary color components - red, green and blue - and a signal from each is processed by one of the three CCDs to provide rich and detailed video images.</p>

<p>"Consumers will be amazed by the richness and clarity of the video taken with one of these new High Definition camcorders, as this is quality they are accustomed to getting when they go to the movies," Alex Fried, National Marketing Manager, Imaging, Panasonic Consumer Electronics Company. "Panasonic is bringing the benefits of High Definition right into the living room, so anyone can enjoy high-quality videos in the comforts of their home. And with new features such as Face Detection and Intelligent Shooting Guide, these new intuitive technologies help consumers easily produce video content they would be proud to view in High Definition."</p>

<p>The SD9 and HS9 are the world's first**** camcorders to feature Face Detection for use in recording moving images. Face Detection automatically locates any faces in the picture and adjusts the exposure, contrast and skin tone to achieve optimal results. Thus, even when the faces are backlit or the lighting is low, faces are clear and not lost in the shadows.</p>

<p>Panasonic's Intelligent Shooting Guide makes important shooting features that are available on the camcorder, but often go unused by the consumer, more intuitive and obvious. For instance, with Intelligent Shooting Guide, the SD9 and HS9 will detect when shooting conditions are poor and then display tips on the LCD as a way for the user to correct the error before recording the content. Intelligent Shooting Guide can detect errors in the following common shooting situations: when the camcorder is panning too quickly or tilted; when the user's hand is swinging; when the subject is backlit; or the lighting is too low. Now common shooting errors can be noticed prior to the playback and corrected accordingly.</p>

<p>The HDC-SD9 records on both SD and SDHC Memory Cards, and is even more compact than its predecessor, the HDC-SD5 model. After recording with an SD9, the SD Memory Card can be inserted into the SD card slot on certain Panasonic PZ Series VIERA HDTV's for easy, instant playback of High Definition video. Moreover, both the SD9 and HS9 are compatible with Panasonic's VIERA Link. and can connect either to a VIERA HDTV via an HDMI cable, or the user can operate the camcorder using the TV remote control and following on-screen prompts.</p>

<p>The HS9 is a hybrid model and can record full-High Definition images onto either an SD (or SDHC) Memory Card or to its built-in 60-GB hard disk. A 16-GB SDHC Memory Card*** can hold approximately six hours of video, and the 60-GB hard disk can hold around 23 hours (both in HE mode). This gives the HS9 a total capacity of approximately 29 hours of full-HD recording. In addition, the HS9 can copy recorded video images from the SD Memory Card to the hard disk, or vice versa, with the push of one button, so no computer is needed.</p>

<p>Other new features on both the HDC-SD9 and HDC-HS9 include:</p>

<p>-- Intelligent Shooting Selection Playback - Helps prevent recording errors being viewed during playback, as it will automatically skip over any shots that have serious errors, such as hand swing, fast panning, out-of-focus images or shots of the ground taken when the user forgets to turn off the camcorder.<br />
-- Advanced 5.1-Channel Surround Sound - With five microphones, when recordings are played on a 5.1-channel home theater system, viewers will be surrounded by clear, detailed sound. A Zoom Mic feature lets the microphone get as close as the camera's zoom lens.<br />
-- High-Speed Burst Shooting at 24 Frames/Sec - Ability for the camcorder to take up to 24 consecutive 2.1 megapixel resolution still images in one second or up to 72 consecutive shots in three seconds.<br />
-- World Timer - Handy for travelers this feature makes it easy to set the time when crossing time zones and again when returning home by switching between the two times with a press of a button. Simply select the destination from the display map, and the camcorder is automatically set for that time zone.<br />
-- 0.6-Sec Quick Start - Starts recording just 0.6 second from the time the LCD is opened, making it easier to catch those special moments that come with no warning.</p>

<p>Both models will be available in March 2008 with manufacturer suggested retail prices of $799.95 for the HDC-SD9 and $1099.95 for the HDC-SH9.</p>

<p> * For 3CCD camcorders capable of high-definition recording, as of<br />
 January 6, 2008.</p>

<p> ** In HA/HG mode.</p>

<p> *** "Full-HD video with 1920 x 1080 pixels" in Panasonic home-use<br />
 camcorders refers to video recordings with 1,920 pixels in the<br />
 horizontal direction and 1,080 pixels in the vertical direction.</p>

<p> **** 1 GB = 1,073,741,824 bytes. Usable capacity will be less. SDHC<br />
 Memory Cards can only be used in SDHC host devices, which support the<br />
 SDHC Memory Card.</p>

<p><br />
<B>About Panasonic Consumer Electronics Company</B></p>

<p>Based in Secaucus, N.J., Panasonic Consumer Electronics Company (PCEC), a market and technology leader in High Definition television, is a Division of Panasonic Corporation of North America, the principal North American subsidiary of Matsushita Electric Industrial Co. Ltd. (NYSE:MC) and the hub of Panasonic's U.S. marketing, sales, service and R&D operations. Panasonic's exclusive Panasonic Plasma Concierge customer support program (888-972-6276) is administered through its Virginia-based Call Center, recognized as a Certified "Center of Excellence" by the Center for Customer-Driven Quality(TM) at Purdue University. Information about Panasonic products is available at www.panasonic.com. Additional company information for journalists is available at www.panasonic.com/pressroom.</p>

<p>Source: Panasonic</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  6, 2008 11:37 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 870
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
 				AND entry_id <> 870
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/panasonic_unveils_two_new_full-high_definition_camcorders.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
