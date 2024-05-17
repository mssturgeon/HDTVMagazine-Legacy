<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1428";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1428 AND placement_is_primary = 1";
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
	<meta name="keywords" content="share station, dual format, everio line, high definition, hard disk, jvc, JVC, AVCHD, avchd, new, everio, Everio, video, camcorders, high, station, technology, models, full, playback, Full, mpeg, MPEG, color, discs" />
	<meta name="description" content="JVC has added three new high definition camcorders to its HD Everio line, including a pair of dual-format models, one of which records up to 50 hours of AVCHD Full HD video.

Two new models, the GZ-HD40 and GZ-HD30, are the world's first AVCHD / MPEG-2 dual-format camcorders, allowing access to the superior long time compression afforded by AVCHD, as well as MPEG-2's superior editing and post-production environment. Both offer..." />
	<title>HDTV Magazine Bulletins - New JVC HD Everio Line Includes World's First 50-Hour AVCHD Camcorder and Dual-Format Models</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('New JVC HD Everio Line Includes World\'s First 50-Hour AVCHD Camcorder and Dual-Format Models'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/06/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">New JVC HD Everio Line Includes World's First 50-Hour AVCHD Camcorder and Dual-Format Models</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>June 16, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/06/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/06/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/06/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/06/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models.php&amp;phase=2&amp;title=New%20JVC%20HD%20Everio%20Line%20Includes%20World%27s%20First%2050-Hour%20AVCHD%20Camcorder%20and%20Dual-Format%20Models&amp;bodytext=JVC%20has%20added%20three%20new%20high%20definition%20camcorders%20to%20its%20HD%20Everio%20line%2C%20including%20a%20pair%20of%20dual-format%20models%2C%20one%20of%20which%20records%20up%20to%2050%20hours%20of%20AVCHD%20Full%20HD%20video.%0A%0ATwo%20new%20models%2C%20the%20GZ-HD40%20and%20GZ-HD30%2C%20are%20the%20world%27s%20first%20AVCHD%20%2F%20MPEG-2%20dual-format%20camcorders%2C%20allowing%20access%20to%20the%20superior%20long%20time%20compression%20afforded%20by%20AVCHD%2C%20as%20well%20as%20MPEG-2%27s%20superior%20editing%20and%20post-production%20environment.%20Both%20offer...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">New JVC HD Everio Line Includes World's First 50-Hour AVCHD Camcorder and Dual-Format Models</p>

<p><B>WAYNE, N.J., June 16 /PRNewswire/</B> -- JVC has added three new high definition camcorders to its HD Everio line, including a pair of dual-format models, one of which records up to 50 hours of AVCHD Full HD video.</p>

<p>Two new models, the GZ-HD40 and GZ-HD30, are the world's first AVCHD / MPEG-2 dual-format camcorders, allowing access to the superior long time compression afforded by AVCHD, as well as MPEG-2's superior editing and post-production environment. Both offer 1920 x 1080 Full HD recording in both formats to a 120GB (GZ-HD40) or 80GB (GZ-HD30) internal hard disk drive. The third new model, the GZ-HD10, offers 1440 x 1080 recording in the AVCHD format to a 40GB hard drive.</p>

<p>Technologies behind the scenes include new CMOS imaging chips with interpolation technology, the HD Gigabrid Duo chip that includes codecs for AVCHD in all camcorders and MPEG-2 in the GZ-HD40 and GZ-HD30 as well as noise reduction technology, and 1920 x 1080/60P output for all camcorders via HDMI(TM) (V.1.3 with x.v.Color). Ease of use innovations include Intelligent Grouping, which sorts and groups together similar themed scenes for easier access, and Digest Playback, which creates a video montage of scenes, much like a "coming attractions" clip, when they're burned to a DVD.</p>

<p>JVC designed the GZ-HD30 to meet the needs of a broad segment of people who shoot video, including family users. More compact than the GZ-HD40, it offers all of the performance and nearly all of the features of that model, except it has a slim 80GB internal hard disk for a maximum 33 hours of Full HD recording, and does not come with a docking station. As of June 2008, it is the world's smallest Full HD camcorder with 1.8" HDD.</p>

<p>JVC provided the GZ-HD40 with a 120GB hard disk drive for up to 50 hours of AVCHD Full HD video recording, and a docking station to meet the requirements of the prosumer or high-end enthusiast.</p>

<p>JVC designed the small sized HD Everio GZ-HD10 thinking of the needs of budget-conscious users, yet it offers a high quality KONICA MINOLTA HD LENS and a 40GB hard disk that provides up to 16 hours of high-definition recording. The GZ-HD10's 1440x1080 AVCHD recordings are also up converted for playback at 1920 x 1080/60P via HDMI.</p>

<p>All three of the new HD Everio camcorders can connect to the optionally available CU-VD50 Everio SHARE STATION with stand-alone playback. The new HD Everios can also connect to the JVC CU-VD3 SHARE STATION to offer an even more cost-efficient archiving and playback solution. The combinations offer the world's first PC-less solution for AVCHD and MPEG-2 burning and playback, with AVCHD DVD discs that can be shared with commonly available AVCHD compatible Blu-Ray players.</p>

<p>The image sensors used in the new HD Everio line are newly developed small size but high resolution 1/3" and 1/4.5" CMOS chips. This CMOS uses proprietary interpolation technology based on what JVC developed for its 3CCD cameras. This algorithm makes it possible to generate virtual pixel data from the red, green and blue physical pixels, thereby providing image information that actually surpasses what's required for 1920 x 1080 Full HD imaging, or for 1440 x 1080 imaging in the GZ-HD10.</p>

<p>JVC's HD Gigabrid video engine, which processes in full 1920 x 1080 progressive video, was the result of years of experience in signal-processing technology, and uses five digital noise-reduction algorithms as well as signal processing to improve horizontal scan resolution by approximately 20 percent over JVC's previous interlace technology, for an extremely clear and sharp image.</p>

<p>New this year with the HD Gigabrid Duo engine is its further advanced integration, adding the AVCHD codec (GZ-HD40/30/10), and incorporating all functions of the previous HD Gigabrid engine (including MPEG-2 codec for GZ-HD40/30), as well as the USB Host controller function for use with an Everio SHARE STATION.</p>

<p>Enabling output of a 1080p 60 fps progressive signal on all three models allows recordings to be enjoyed on high-end displays. Using the same high power Genessa technology as in JVC advanced displays, conversion to 1920 x 1080p at 60 fps provides seamless natural video, free of motion judder during fast action activities, jaggy lines on angles, and moire patterns on fine detail when zooming. 1920 x 1080 HD discs played back from CU-VD50 SHARE STATION or via the camera from CU-VD3 SHARE STATION are also converted to 60 fps progressive.</p>

<p>The new JVC HD Everio camcorders record using x.v.Color(TM) technology. The universal standard xvYCC, known as x.v.Color, provides more accurate color reproduction with more detail and shades that look more natural to the human eye. It can display 100 percent of the colors that the human eye is capable of perceiving, whereas the traditional sRGB system can only display approximately 55 percent. The difference in color reproduction performance is especially noticeable in greens and yellows. These new HD Everio camcorders output to televisions directly using HDMI(TM) (V.1.3. with x.v.Color).</p>

<p>All three models are equipped with USB2.0 and HDMI digital interfaces on the camcorder. The Docking Station supplied with the GZ-HD40 is equipped with USB2.0 and i.LINK (IEEE 1394).</p>

<p>For editing and archiving via PC, the new HD Everio models come with the CyberLink BD Solution software suite for Windows. This includes "PowerDirector(TM) 6 NE" for HD video editing, "PowerProducer(TM) 4 NE" for authoring high definition Blu-ray discs, AVCHD discs and DVD-Video discs, "PowerCinema(TM) NE for Everio" for HD file management and playback, and "PowerDVD(TM) 7 NE" for playback of AVCHD discs.</p>

<p>The GZ-HD40/HD30 also come with a plug-in that allows HD Everio's MPEG-2 files to be used with Apple iMovie HD 6 and Final Cut Pro 5 or 6 video software for the Macintosh. For all camcorders, AVCHD files can be used with Apple's AVCHD applicable software without a plug-in for the Macintosh.</p>

<pre>
Model     National Ad Value     Available
GZ-HD40     $1,299.95             August
GZ-HD30     $999.95               August
GZ-HD10     $799.95               August
</pre>

<p><br />
<B>About JVC Company of America</B></p>

<p>JVC Company of America, headquartered in Wayne, New Jersey, is a division of JVC Americas Corp., a wholly-owned subsidiary of Victor Company of Japan Ltd., and a holding company for JVC companies located in North and South America. JVC distributes a complete line of video and audio equipment, including high definition displays, camcorders, DVD players and recorders, home and portable audio equipment, mobile entertainment products and recording media. For further product information, visit JVC's Web site at http://www.jvc.com/ or call 800-526-5308.<br />
Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20080616/NYM049<br />
AP Archive: http://photoarchive.ap.org/<br />
AP PhotoExpress Network: PRN5<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: JVC Company of America </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>June 16, 2008 09:07 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1428
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
 				AND entry_id <> 1428
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/06/new_jvc_hd_everio_line_includes_worlds_first_50-hour_avchd_camcorder_and_dual-format_models.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
