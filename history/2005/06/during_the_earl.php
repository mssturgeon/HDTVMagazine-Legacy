<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 93";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 93 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="frame rate, compensated interpolation, motion compensated, temporal aliasing, latter won, motion, picture, frame, any, viewers, HDTV, hdtv, ntsc, much, mit, quality, NTSC, MIT, pictures, rate, study, compared, made, subject, Note" />
	<meta name="description" content="WEll before the ATSC standard was set by the FCC in late 1996, even before proponent systems were built and tested, many things leaned heavy against the HDTV movement. Not the least of these weights was a consumer evaluation of..." />
	<title>HDTV Magazine Archive &amp; History - The Appearance of TV Displays...</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_appearance_of_tv_displays';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Appearance of TV Displays...'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/the_appearance_of_tv_displays.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Appearance of TV Displays...</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 17, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/the_appearance_of_tv_displays.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/the_appearance_of_tv_displays.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/the_appearance_of_tv_displays.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/the_appearance_of_tv_displays.php&amp;phase=2&amp;title=The%20Appearance%20of%20TV%20Displays...&amp;bodytext=WEll%20before%20the%20ATSC%20standard%20was%20set%20by%20the%20FCC%20in%20late%201996%2C%20even%20before%20proponent%20systems%20were%20built%20and%20tested%2C%20many%20things%20leaned%20heavy%20against%20the%20HDTV%20movement.%20Not%20the%20least%20of%20these%20weights%20was%20a%20consumer%20evaluation%20of...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>WEll before the ATSC standard was set by the FCC in late 1996, even before proponent systems were built and tested, many things leaned heavy against the HDTV movement. Not the least of these weights was a consumer evaluation of HDTV. A study was constructed that contrasted HDTV with studio-quality standard 525 line television. The conclusion of that study was that the public didn't see a big advantage to HDTV, cetainly not enough to spend a lot of money on it. The controversial evaluation proved to be a staggering blow against the advocates for HDTV. These blows were proving effective. Money for various parts of HDTV's advancement had to be raised from managers who were far more interested in the bottom line of the next quarter than in pretty pictures that had no obvious path to the consumer's eye. Advocates had no choice but to try descrediting the research saying it was clearly biased by technical types who had their own ax to grind. MIT and the MediaLab (under Nicholas Negroponte) did hold a bias against HDTV, at least as it was being propsed at the time. Much of that bias could be traced back to anti-Japanese and anti-analog sentiments. Japan who had a big jump on HDTV. The Americans had no industrial stake in it if the Japanese system was to be adopted as it was being proposed. The Media Lab favored open digitally-based systems and the NHK 1125/60 analog production design could hardly be declared open. Digital capture and transmission had not yet been considered. As it had been for 60 years with analog NTSC whatever the production standard was to be so would the receiver and display standards be.</em> </p>

<p>This paper was first presented by Professor William Schribers (now retired)from MIT's Electrical Engineering school. During his teaching tenure Professor Schreiber was considered one of the world's more respected display experts.  <br />
 <br />
<strong>The Appearance of TV Displays... </strong></p>

<p>THE WILLIAM SCHREIBER FILES...<br />
William Schreiber is noted engineer and is now a retired professor of electrical engineer MIT. He was seldom in agreement with any of the standards that were proposed and tested as he believed that a layer coded standard would make the transition from good to poor transmission environments more graceful.</p>

<p>________________________________</p>

<p>In the course of the Advanced Television Research Program at MIT, which I headed from 1983 until I retired in 1990, Russ Neuman, now at U Penn, directed an audience study at the Liberty Tree Mall in Danvers, Mass. </p>

<p>Viewers were enticed out of the Mall with a small gift certificate and asked to look at TV and listen to audio and answer some questions. The operation was completely anonymous; the viewers did not know it was MIT and were not told what they were observing. </p>

<p>Among other things, we compared "studio quality" 1125 I to NTSC with a variety of subject matter. (There was only a slight preference for HDTV and little willingness to spend much for it, but this is not the point I am trying to make here.) </p>

<p>Even with very careful professional attention, it was very hard to get the picture contrast, brightness, and color close enough on the two displays so that an unbiased judgment could be made as to quality as dependent only on resolution and aspect ratio. (The two pictures, when compared side-by-side, were the same height but different widths.) I had the feeling that turning any knob that affected picture appearance on either display could have made either picture look better than the other. Note that we controlled for viewing distance and for many personal characteristics of the viewers. We also showed the pictures one by one and asked for a comparison with viewers' TVs at home. </p>

<p>One conclusion from this part of the study was that picture quality at home was controlled primarily by analog channel impairments, not scanning standards or bandwidth. Everybody thought that the pictures we showed were much better than they had at home. Of all the quality factors studied, the most important was picture size. Most viewers preferred larger pictures even when they said the smaller picture was sharper. </p>

<p><strong>Temporal Aliasing </strong><br />
Another part of the ATRP study was motion rendition. In a few days, I shall post citations to two PhD theses on this subject, which may be ordered from the MIT library. I have no doubt that, at typical motion speed, temporal aliasing is always present at any reasonable frame rate. Cinematographers know this and adjust camera technique appropriately. Video people, who are really using 60 fps in NTSC, can move the camera much faster without causing much damage to the picture. In work by Ed Krause, now at Imedia in SF, a demo was made testing various ways to render motion. One was a computer-generated "bouncing ball" demo, in which 3/2 conversion to NTSC was compared with motion-compensated interpolation. </p>

<p>The latter won, hands down. In another, video was made of a close-up of a wristwatch, causing it to move horizontally with sinusoidally varying velocity. We compared frame repetition, motion-blur, and motion compensation. Again, the latter won easily. Motion blur did not appreciably reduced the motion judder of frame repetition. Note that when the eye is tracking motion on the TV screen, any attempt to reduce judder by blurring greatly reduces perceived sharpness. Some video is deliberately shot with very short exposure time per field just to retain sharpness of moving objects. ("You can see the stitching on the baseball!") </p>

<p>Krause also converted both 24-fps and 12-fps simulated film to NTSC using motion compensation. The results were very good, altho the latter was not quite as good as the former, which was essentially perfect. Note that if motion compensated interpolation is to be used at the receiver without transmission of motion vectors, then the picture should not be blurred at all before transmission. In another thesis by Dennis Martinez, motion-compensated interpolation was used to convert any frame rate to any other frame rate. </p>

<p>The subject was a closeup of a person's face while speaking. (The audio was also successfully converted with duration changes of 20% or so.) The results were essentially perfect with large changes in frame rate and corresponding changes in the speed of motion. <br />
The Appearance of TV Displays </p>

<p>In the course of the Advanced Television Research Program at MIT, which I headed from 1983 until I retired in 1990, Russ Neuman, now at U Penn, directed an audience study at the Liberty Tree Mall in Danvers, Mass. </p>

<p>Viewers were enticed out of the Mall with a small gift certificate and asked to look at TV and listen to audio and answer some questions. The operation was completely anonymous; the viewers did not know it was MIT and were not told what they were observing. </p>

<p>Among other things, we compared "studio quality" 1125 I to NTSC with a variety of subject matter. (There was only a slight preference for HDTV and little willingness to spend much for it, but this is not the point I am trying to make here.) </p>

<p>Even with very careful professional attention, it was very hard to get the picture contrast, brightness, and color close enough on the two displays so that an unbiased judgment could be made as to quality as dependent only on resolution and aspect ratio. (The two pictures, when compared side-by-side, were the same height but different widths.) I had the feeling that turning any knob that affected picture appearance on either display could have made either picture look better than the other. Note that we controlled for viewing distance and for many personal characteristics of the viewers. We also showed the pictures one by one and asked for a comparison with viewers' TVs at home. </p>

<p>One conclusion from this part of the study was that picture quality at home was controlled primarily by analog channel impairments, not scanning standards or bandwidth. Everybody thought that the pictures we showed were much better than they had at home. Of all the quality factors studied, the most important was picture size. Most viewers preferred larger pictures even when they said the smaller picture was sharper. </p>

<p>Temporal Aliasing <br />
Another part of the ATRP study was motion rendition. In a few days, I shall post citations to two PhD theses on this subject, which may be ordered from the MIT library. I have no doubt that, at typical motion speed, temporal aliasing is always present at any reasonable frame rate. Cinematographers know this and adjust camera technique appropriately. Video people, who are really using 60 fps in NTSC, can move the camera much faster without causing much damage to the picture. In work by Ed Krause, now at Imedia in SF, a demo was made testing various ways to render motion. One was a computer-generated "bouncing ball" demo, in which 3/2 conversion to NTSC was compared with motion-compensated interpolation. </p>

<p>The latter won, hands down. In another, video was made of a close-up of a wristwatch, causing it to move horizontally with sinusoidally varying velocity. We compared frame repetition, motion-blur, and motion compensation. Again, the latter won easily. Motion blur did not appreciably reduced the motion judder of frame repetition. Note that when the eye is tracking motion on the TV screen, any attempt to reduce judder by blurring greatly reduces perceived sharpness. Some video is deliberately shot with very short exposure time per field just to retain sharpness of moving objects. ("You can see the stitching on the baseball!") </p>

<p>Krause also converted both 24-fps and 12-fps simulated film to NTSC using motion compensation. The results were very good, altho the latter was not quite as good as the former, which was essentially perfect. Note that if motion compensated interpolation is to be used at the receiver without transmission of motion vectors, then the picture should not be blurred at all before transmission. In another thesis by Dennis Martinez, motion-compensated interpolation was used to convert any frame rate to any other frame rate. </p>

<p>The subject was a closeup of a person's face while speaking. (The audio was also successfully converted with duration changes of 20% or so.) The results were essentially perfect with large changes in frame rate and corresponding changes in the speed of motion. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 17, 2005 10:23 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 93
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
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 93
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/the_appearance_of_tv_displays.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
