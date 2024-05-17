<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 75";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 75 AND placement_is_primary = 1";
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
	<meta name="keywords" content="direct view, lcd projection, screen sizes, view lcd, dlp projection, HDTV, hdtv, lcd, screen, LCD, projection, view, CRT, crt, performance, sets, best, technology, images, direct, good, image, price, however, plasma" />
	<meta name="description" content="After the meeting, we were whisked back to a room deep in the bowels of the labs by one of the developmental engineers. He said he wanted to show us what color television could do if there were virtually no technical limitations. Just before entering the room, he said, in a matter-of-fact tone, &quot;You will never see images reproduced better than this.&quot; Well, never is a long time, but to date, he was right.
" />
	<title>HDTV Magazine Articles - Ed's View - The Best HDTV Display</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_the_best_hdtv_display';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - The Best HDTV Display'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_the_best_hdtv_display.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - The Best HDTV Display</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>June 14, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_the_best_hdtv_display.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_the_best_hdtv_display.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_the_best_hdtv_display.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_the_best_hdtv_display.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20The%20Best%20HDTV%20Display&amp;bodytext=After%20the%20meeting%2C%20we%20were%20whisked%20back%20to%20a%20room%20deep%20in%20the%20bowels%20of%20the%20labs%20by%20one%20of%20the%20developmental%20engineers.%20He%20said%20he%20wanted%20to%20show%20us%20what%20color%20television%20could%20do%20if%20there%20were%20virtually%20no%20technical%20limitations.%20Just%20before%20entering%20the%20room%2C%20he%20said%2C%20in%20a%20matter-of-fact%20tone%2C%20%22You%20will%20never%20see%20images%20reproduced%20better%20than%20this.%22%20Well%2C%20never%20is%20a%20long%20time%2C%20but%20to%20date%2C%20he%20was%20right.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em><strong>Ed's view</strong></em></p>

<p><br />
<strong>THE BEST HDTV DISPLAY</strong></p>

<p>by Ed Milbourn</p>

<p>First, let me emphasize that virtually all HDTV displays are excellent and most all pay off on the promise of a true HDTV image.  But some are created more equal than others, at least in my view.</p>

<p>I first saw HDTV in the early '80's at the RCA Laboratories (David Sarnoff Research Center in Princeton, NJ). A small clutch of RCA Consumer Electronics types occasionally would travel there from Indianapolis to see what was new and exciting at the Labs that may be commercialized. However, this particular meeting was not at all about HDTV.  It was, as I recall, something to do with demonstrations of advanced features for the ill-fated RCA CED Video Disc system.</p>

<p>After the meeting, we were whisked back to a room deep in the bowels of the labs by one of the developmental engineers. He said he wanted to show us what color television could do if there were virtually no technical limitations. Just before entering the room, he said, in a matter-of-fact tone, "You will never see images reproduced better than this." Well, never is a long time, but to date, he was right.</p>

<p>In the middle of the large room was a huge direct-view cathode ray tube (CRT) monitor with an approximate 34" diagonal screen. The images blew our minds! A special high-resolution camera located at the back of the room generated those images. The camera was trained on a set with a variety of objects, some still and some in motion.  The images on the screen looked better than the live ones.</p>

<p>That experience caused those images to be indelibly impressed in my mind as the "gold standard" for HDTV images. So, my view of the "best" HDTV images is somewhat prejudiced by those "mind blowing" CRT-based pictures. With that in mind, let's take a critical look at the comparative HDTV quality produced by the currently available HDTV sets.</p>

<p><strong>Direct-view CRT</strong><br />
Direct-view CRT sets deliver an extremely good HDTV picture. They are, however, somewhat compromised by the loss of edge definition due to the wide deflection angles needed to keep the depth of the cabinets reasonable. But the primary downside of the direct view CRT sets is the limited screen size. The largest 16x9 diagonal CRT screen size is about 34 inches. To optimize the maximum effect of HDTV, the viewer should be no more than three times the picture height of the screen. Therefore, a 34-inch diagonal viewing surface places the viewer too close to the screen for the average U.S. living or recreation room (although the bedroom may be OK). However, a direct view HDTV CRT set remains the best buy for the money.</p>

<p><strong>CRT Projection</strong><br />
It is becoming difficult to find a selection of CRT projection sets at the major retailers. In the past year almost all retailers have moved to some type of solid-state microdisplay technology for HDTV projection.  However, there are a few left, mostly in monitor format (no included HDTV tuner). If one can be found, it is an extremely good value to deliver a true HDTV "experience" in the average U.S. living room. However, in some instances, compromises have been made in CRT projection performance that is worthy of mention. These compromises are in the form of cheap, non-HDTV quality tubes, optics and/or electronics. Some manufacturers have simply coupled 16x9 screens with SDTV tubes and optics, added component video inputs to SDTV video circuitry and called them HDTV "ready" at very attractive price points. Although the result is usually better than SDTV quality images. In many cases they would not pass muster for HDTV. You get what you pay for. </p>

<p><strong>Plasma</strong><br />
Wow! Has this technology come a long way in a short time. Plasma produces a very crisp image and is getting better every generation (year).  With each generation, performance goes up and prices go down, which is the right direction. However, there remains work to do on both ends to make this technology truly price and performance effective.  Performance continues to suffer from white compression (blurry high lights), black level management (black areas are reproduced as dark gray) and switching artifacts (image noise very noticeable in low lights). Prices remain high compared with screen sizes using competitive technology. I believe a 30% increase in performance coupled with a 30% reduction in price for any given screen size will make this technology a viable contender for the HDTV mass market. In the meantime there is no reason to invest in plasma at this time unless the flat form factor is the major consideration.</p>

<p><strong>Direct View LCD  </strong><br />
At this time, direct view LCD probably produces the best images at lower screen sizes (42" diagonal and below). There is very little direct view LCD in screen sizes above 42", as there are almost no plasma screens below that size. But that will change with LCD in future generations. The battle will be between LCD and Plasma within a very few years, and I believe LCD will win in both performance and price categories.  </p>

<p><strong>Digital Light Processor (DLP projection)</strong><br />
Despite the fact that DLP projection (as now marketed) is an electromechanical technology, with over one million flopping little bitty mirrors and a spinning color filter, it produces a fantastic HDTV image. I do have a philosophical problem with electromechanical television, which I thought we put to rest in 1953, but here it is in spite of my misgivings. DLP projection produces an amazingly excellent HDTV picture with virtually no switching artifacts. The image can be very bright (probably the brightest of the group), with good dynamic range (with a little white compression) and excellent black level management. It is cost-effective, available in several screen sizes, and embraced by most of the major manufacturers. </p>

<p><strong>LCD Projection (the winner)</strong><br />
In my view LCD projection simply produces the best large screen image on the market. It is probably slightly more expensive and slightly dimmer than DLP, but not enough to matter. The chances are very good that when you gaze on the array of HDTV sets on the retailers' showroom floor, your eyes will immediately gravitate to an LCD projection display. These displays appear to have the best video dynamic range, black level management, and minimum display-generated artifacts. There are other LCD based technologies becoming available such as LcOS and DILA. These technologies produce similar image quality.</p>

<p><strong>The Future</strong><br />
To a great extent, the future is here. Again, they all produce excellent HDTV - albeit, some more "excellent" than others.  Incremental performance increases along with incremental price decreases will always take place. One such upcoming incremental increase in performance is 1080p. 1080p allows the combination of the optimized detail of 1080i with the motion artifact reduction optimization of 720p. Whether the initial premium for 1080p is worth the price is difficult to say - you be the judge. In my opinion, it isn't.   </p>

<p>In my opinion, the future HDTV displays for home viewing will incorporate some type of flat-panel scheme. I don't think it will be plasma. Another technology being developed showing great promise is called "field emission" or "cold-cathode." Another name for this technology is SED (Surface-conductive Electron-emitter Display).  Essentially, with field emission displays, each pixel consists of a very small picture tube, with emitted electrons striking a phosphor screen. This technology has the potential of high performance, low cost, and easy scalability to large screen sizes. And it efficiently generates its own light - just like a CRT. Seems we are back to where we started.  </p>

<p>Ed</p>

<p>____________________<br />
<strong>About Ed Milbourn</strong><br />
After graduating from Purdue University with degrees in Electrical Engineering and Industrial Education in 1961 and 1963 respectively, Ed Milbourn joined the RCA Home Entertainment Division in 1963.  During his thirty-eight year career with RCA (later GE and Thomson multimedia), Mr. Milbourn held the positions of Field Service Engineer, Manager of Technical Training and Manager of Sales Training.  In 1987, he joined Thomson's Product Management group as Manager of Advanced Television Systems Planning, with responsibilities including Digital Television and High Definition Television Product Management.  Mr. Milbourn retired from Thomson multimedia in December 2001, and is now a Consumer Electronics Industry consultant. </p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>June 14, 2005 09:30 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 75
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
 				AND entry_id <> 75
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_the_best_hdtv_display.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
