<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 183";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 183 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
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
	<meta name="keywords" content="lines scanned, display system, pixel count, per second, normal viewing, fps, lines, system, video, image, resolution, television, scanning, picture, scanned, format, better, bandwidth, greater, display, material, pixel, problem, ATSC, progressive" />
	<meta name="description" content="Yes, there is a new kid on the HDTV block. And it is (drum roll) -1080p. 1080p is one of those incremental improvements in HDTV to which I have been referring to in my previous articles. 1080p, of course, means 1080 horizontal lines scanned one after the other, or progressively. This is apposed to 1080i that denotes 1080 lines scanned in an interlaced fashion, with the odd number of lines scanned initially and the even number of lines then scanned and positioned, or &quot;interlaced,&quot; between the odd lines.

So, is 1080p better than 1080i? Is it better than 720p? The answer to both of these questions is... " />
	<title>HDTV Magazine Articles - Ed's View - The New Kid On The Block</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_the_new_kid_on_the_block';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - The New Kid On The Block'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/08/eds_view_-_the_new_kid_on_the_block.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - The New Kid On The Block</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>August 11, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/08/eds_view_-_the_new_kid_on_the_block.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/08/eds_view_-_the_new_kid_on_the_block.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/08/eds_view_-_the_new_kid_on_the_block.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/08/eds_view_-_the_new_kid_on_the_block.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20The%20New%20Kid%20On%20The%20Block&amp;bodytext=Yes%2C%20there%20is%20a%20new%20kid%20on%20the%20HDTV%20block.%20And%20it%20is%20%28drum%20roll%29%20-1080p.%201080p%20is%20one%20of%20those%20incremental%20improvements%20in%20HDTV%20to%20which%20I%20have%20been%20referring%20to%20in%20my%20previous%20articles.%201080p%2C%20of%20course%2C%20means%201080%20horizontal%20lines%20scanned%20one%20after%20the%20other%2C%20or%20progressively.%20This%20is%20apposed%20to%201080i%20that%20denotes%201080%20lines%20scanned%20in%20an%20interlaced%20fashion%2C%20with%20the%20odd%20number%20of%20lines%20scanned%20initially%20and%20the%20even%20number%20of%20lines%20then%20scanned%20and%20positioned%2C%20or%20%22interlaced%2C%22%20between%20the%20odd%20lines.%0A%0ASo%2C%20is%201080p%20better%20than%201080i%3F%20Is%20it%20better%20than%20720p%3F%20The%20answer%20to%20both%20of%20these%20questions%20is...%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Yes, there is a new kid on the HDTV block. And it is (drum roll) -1080p. 1080p is one of those incremental improvements in HDTV to which I have been referring to in my previous articles. 1080p, of course, means 1080 horizontal lines scanned one after the other, or progressively. This is opposed to 1080i that denotes 1080 lines scanned in an interlaced fashion, with the odd number of lines scanned initially and the even number of lines then scanned and positioned, or "interlaced," between the odd lines.</p>

<p>So, is 1080p better than 1080i? Is it better than 720p? The answer to both of these questions is yes, it can be. But alone is it enough to blow up your skirt and entice you to fork out the initial added premium? Probably not. Allow me to explain:</p>

<p>Usually, "P" is better than "I," and more lines are better than fewer lines. Understand that progressive scan does not improve the picture resolution. [OK, the purists will site the so-called "Interlace Factor," that causes a loss of vertical resolution due to the interlace scanning structure. But this effect is virtually invisible at normal viewing distances. So, bug off! Progressive scan does, however, eliminate temporal artifacts, such as the "shimmer" that occurs when interlaced images are panned vertically. The progressively scanned image also appears "smoother," more "film-like," due to the virtual elimination of any visible scanning line structure. However, there is a small fly in the ointment.</p>

<p>Historically, there were three basic reasons an interlaced scanning scheme was adopted for the 525-line/30 frames per second (fps) U.S. TV video format. These were:  the minimization of "flicker," which is objectionable at 30 sequential frames per second; the minimization of the video bandwidth required by doubling the amount of time to construct each frame; and the easier horizontal deflection design requirements by operating at 15.75Khz instead of 31.50Khz. These factors reflected the state-of-the-art when the standard was codified in 1941.  </p>

<p>Today, progressive scanning at 60fps eliminates the flicker problem, and deflection (or digital device switching) speeds present no real problem, but broadcast bandwidth constraints remain. This is why of the 18 different video formats specified by the Advanced Television System Committee (ATSC)' 1080p at 60fbs is not supported, but 1080p at 30fps is supported. It would take twice the bandwidth to transmit 1080p at 60fps. 720p at 60fps can be supported because the reduced number of pixels-per-frame is within the bandwidth constraints of the ATSC system.</p>

<p>So, unless somebody comes up with a bunch more spectrum, or ATSC scraps MGEG2 for MGEG4 (not likely), we will not see 1080p at 60fps transmitted. Of course, it is possible to see 1080p at 30fps transmitted (assuming the camera chain can handle it), or authored for DVD and/or video games. At some point, thanks to MPEG4, many of these sources, except broadcast video, may offer a full 1080p format at 60fps. In the meantime it is up to the 1080p display system to deal with the flicker problem presented by 1080p at 30fps. And this is the point at which we must be very careful with 1080p.</p>

<p>It is always optimum to reproduce video material in the same format in which it is originated. Obviously, the optimum production and delivery format for 1080p is with a 60 per second frame rate.  Although some material will be produced and delivered at 1080p 60fps, such as some future HD DVD's and games, most of the HD material that will be delivered - in the foreseeable future - in the ATSC standards of 1080i 30fps or 1080p 30fps. Therefore, for the most part, the display system must convert the 1080p 30fps material to 60fps.  This means the display system must incorporate some sort of up-conversion (or line interpolation) process in order to add another 1/60th of a second's worth of 1080 lines to emulate 60fps.  </p>

<p>Although Interpolation algorithms have greatly improved in the past few years, none of them are perfect. The most common problem of badly design up-converters is the introduction of horizontal movement artifacts - the dreaded motion smear. Yuck!</p>

<p>The newer up-converter algorithms have sufficient sophistication, memory and bandwidth to produce extremely accurate interpolated lines.</p>

<p>Another dimension of up-conversion is the capability of the system to handle other than primary scanning rates. In other words, how well it processes and displays native 480i, 480p and 720p material. The ability of digitally switched displays - such as DLP, LCoS, Plasma etc., - can have a significant advantage over CRT's relative to scan conversion. This is because the CRT's deflection is most economically operated at a single rate regardless of the signal's scanning format while digital display rates can be easily switched.</p>

<p>So, that brings us to the final question: does a 1080p image display a noticeably higher detailed image that 720p under normal viewing conditions?  Not really. It so happens that when a one mega-pixel or greater image is viewed from a distance of three times the picture height or greater, it is almost impossible to discern any difference in picture resolution as the pixel count increases.  This is because at those viewing distances, the human eye/brain system's perception of resolution becomes essentially "flat" at above one mega-pixel. It is even flatter if the image is in motion, as the brain requires only 1/3rd of the static pixel count to produce the same perception of resolution. Now, if a static image is viewed at closer than three times the picture height - such as in some commercial (e.g. "Computer Aided Design"), medical, text-intensive and/or web-page applications where the image must be studied - then certainly the greater the pixel count, the greater the perceived resolution. As one anonymous engineer remarked in the early days of the development of television,  "After all, television doesn't have to work; it only has to look like it does."</p>

<p>In actuality, at normal viewing distances, the higher picture quality that may be perceived by the new 1080p displays is due mostly by improvements in video signal processing and imaging component performance. Performance parameter improvements such as in brightness, contrast, adaptable aperture correction, noise reduction et. al., added to imaging component improvements in lenses, screens and imagers have a much greater impact on picture performance than 1080p itself. But 1080p is certainly an advance in the right direction. Like any kid, with any kind of luck, it will grow and develop, and add to the increasingly improving quality of HDTV. It will only get better.</p>

<p>Ed<br />
___________________<br />
<strong>About Ed Milbourn</strong><br />
After graduating from Purdue University with degrees in Electrical Engineering and Industrial Education in 1961 and 1963 respectively, Ed Milbourn joined the RCA Home Entertainment Division in 1963. During his thirty-eight year career with RCA (later GE and Thomson multimedia), Mr. Milbourn held the positions of Field Service Engineer, Manager of Technical Training and Manager of Sales Training. In 1987, he joined Thomson's Product Management group as Manager of Advanced Television Systems Planning, with responsibilities including Digital Television and High Definition Television Product Management. Mr. Milbourn retired from Thomson multimedia in December 2001, and is now a Consumer Electronics Industry consultant.</p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>August 11, 2005 03:49 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 183
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 183
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
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
 				<h2>About Ed Milbourn</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/08/eds_view_-_the_new_kid_on_the_block.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
