<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1485";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1485 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (10) {
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
	<meta name="keywords" content="vertical filtering, single pixel, vertical horizontal, vertical lines, horizontal filtering, vertical, filtering, response, lines, horizontal, pixel, displays, CRT, line, crt, burst, detail, HDTV, screen, hdtv, display, single, test, high, content" />
	<meta name="description" content="While rarely mentioned over the decades, vertical filtering, which removes horizontal detail in your image, has been around as long as CRT has and is still used to this day in displays and source scaling. Even with the arrival of DTV in 1998, vertical filtering was commonly used in the video circuits of consumer 1080i HDTV CRT displays. For this article we are looking at vertical or horizontal filtering related to luminance response.

The first step with horizontal and vertical filtering is..." />
	<title>HDTV Magazine Columns - HD Waveform - Vertical and Horizontal Filtering</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hd_waveform_-_vertical_and_horizontal_filtering';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HD Waveform - Vertical and Horizontal Filtering'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_-_vertical_and_horizontal_filtering.php";
		if ($author[img] != '' && 10 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HD Waveform - Vertical and Horizontal Filtering</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>July 31, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_-_vertical_and_horizontal_filtering.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_-_vertical_and_horizontal_filtering.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_-_vertical_and_horizontal_filtering.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_-_vertical_and_horizontal_filtering.php&amp;phase=2&amp;title=HD%20Waveform%20-%20Vertical%20and%20Horizontal%20Filtering&amp;bodytext=While%20rarely%20mentioned%20over%20the%20decades%2C%20vertical%20filtering%2C%20which%20removes%20horizontal%20detail%20in%20your%20image%2C%20has%20been%20around%20as%20long%20as%20CRT%20has%20and%20is%20still%20used%20to%20this%20day%20in%20displays%20and%20source%20scaling.%20Even%20with%20the%20arrival%20of%20DTV%20in%201998%2C%20vertical%20filtering%20was%20commonly%20used%20in%20the%20video%20circuits%20of%20consumer%201080i%20HDTV%20CRT%20displays.%20For%20this%20article%20we%20are%20looking%20at%20vertical%20or%20horizontal%20filtering%20related%20to%20luminance%20response.%0A%0AThe%20first%20step%20with%20horizontal%20and%20vertical%20filtering%20is...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>While rarely mentioned over the decades, vertical filtering, which removes horizontal detail in your image, has been around as long as CRT has and is still used to this day in displays and source scaling. Even with the arrival of DTV in 1998, vertical filtering was commonly used in the video circuits of consumer 1080i HDTV CRT displays. For this article we are looking at vertical or horizontal filtering related to luminance response.</p>

<p>The first step with horizontal and vertical filtering is to understand that horizontal lines on your screen are based on vertical pixel / frequency response and the vertical lines on your screen are based on horizontal pixel / frequency response. For CRT, rear projection horizontal filtering was also used to prevent artifacts from appearing due to the combing effect that could be created when a 1920 vertical response of multiple lines is projected onto a lenticular screen that by its nature and function has its own set of very fine vertical lines. With the new 720p and later 1080p micro-display rear projection technology, such as DLP, LCD, the lenticular screen design was dropped using smooth outer screens for glare reduction instead since they could provide a high contrast response with high resolution, 1280 for 720p or 1920 with 1080p.</p>

<p>While HDTV enthusiasts across the country were crowing over the high resolution HDTV images delivered digitally to their CRT screens, those knowledgeable of imaging science were telling another story of how those same HDTVs weren't earning their HDTV label. Vertical and horizontal filtering was a big part of that story. By 2005, CRT technology had gone by the wayside in favor of lighter and smaller 720p rear projection micro-displays and flat panels. For numerous reasons, the 1920x1080 progressive (1080p) format was put on a pedestal as the holy grail of high definition and HDTV enthusiasts wanted true 1080p displays in their homes. For the 2005-2006 model season these same technologies had finally advanced to 1920x1080 pixel matrices capable of resolving a clear 1920 or 1080 line response. HDTV enthusiasts replaced their old CRT products with this new technology only to find out later that nearly all of the first generation 1080p displays were not actually providing such a response for a variety of reasons and vertical filtering was one of them. Indeed, this first generation of 1080p was all about perception rather than actual performance!</p>

<p>Silicon Optics provided test material for detecting vertical filtering and well known reviewer Gary Merson led the way in exposing this flaw of the new 1080p designs. It wasn't the 1080p display panel at fault but the design of the internal scaler that must convert interlaced 1080i into progressive for a 1080p display panel. Since then, most of the other problems related to achieving a true 1080p response have been tackled, but vertical filtering of 1080i remains with us. With the advent of the new HD disc formats, Blu-ray and HD DVD, anybody can test their display or HD disc player for vertical filtering using <a target="_blank" href="http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php">Silicon Optix HQV Benchmark</a>, which is available in DVD, HD DVD and Blu-ray formats.</p>

<p>Failure of these tests due to vertical filtering does mean there will be a loss of detail. On the other hand, without prior knowledge of this attribute, it is unlikely you would have noticed the loss (recall the enthusiasm of early CRT HDTV adopters) and requires a deeper understanding of burst testing, what is going on and how it affects specific elements of an image.</p>

<p>A luminance burst is a series of alternating black and white lines. The thinner the lines, the higher the frequency of the video signal response. A common response artifact of legacy HDTV CRT rear projection displays was their inability to pass a horizontal 1920 vertical line burst creating the same type of response as the HQV Benchmark 1:1 pixel mapped vertical burst test of 1080 horizontal lines; the lines are missing and instead you have a gray box. By going down one step to two pixels per line, 540, you now see a burst response (although it lacks the contrast response of going down another step to three pixels per line, 270, and lower). An anecdotal conclusion would suggest that those displays failing such a test will not show a single pixel line but yet they do. The reason the 1920 burst won't pass but a 960 burst or single pixel 1920 line does is because the CRT display applies analog filtering somewhere in the circuit and for that filter to work requires a repetition of lines; in essence a video signal of continuous alternating frequency to which the filter is tuned. That does not mean a single pixel line is being delivered to its fullest potential either because the filter will at least soften that response, which is seen as a loss of contrast between white and black for CRT displays. For decades, the vast majority of consumer CRT displays have been using vertical filtering to hide artifacts from the source material, the NTSC system and in many cases a mediocre interlaced vertical sweep circuit design. </p>

<p>For fixed pixel digital displays (FPD), it gets a bit more complicated because in most cases this filter is digital instead of analog and pixel displays respond differently. I test for vertical filtering using a pattern generator that is based on 1:1 pixel mapping for the patterns and use the vertical and horizontal grid pattern of single pixel lines to detect vertical filtering. In this case the filter turns what should be a vertical response single pixel horizontal line into three; the middle line is slightly lower in peak light output than it should be while the other two lines of pixels above and below are greatly reduced further. While this is easily detected by getting close enough to the screen to see individual pixels the perceived effect at the seated viewing position is a single pixel vertical line, even at the <a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=5282">recommended viewing distance</a> of three screen heights for HD. While appearing to have the same brightness/contrast response as the vertical lines of the grid pattern, what you may pick up on is a reduction in a nice hard edge and/or subtle thickening of the line when compared to the horizontal response of vertical lines. Ultimately you are losing detail in whichever plane is being filtered, vertical or horizontal. There is yet another perceptual spin in all this; a great pixel response providing detail in one plane can offset the lack of detail in the other and still provide a perception of detail to your eyes. Making this issue even more complex is to clearly notice the detriments of filtering requires an image that has elements similar to a burst, alternating bright and dark lines at an even 1:1 pixel level. The simplistic finale for this complex technical content is that the artifacts created are specific to the material along with how often they occur. How much effort or money do you want to spend on an artifact that represents a very small portion of any content? How much will it matter to you that at chapter 13 and 1:41 minutes of XYZ movie you can clearly see an artifact for 30 seconds? </p>

<p>The stadium pan of HQV Benchmark provides an excellent example of this because it is the only video content on the disc that has detail similar to a vertical 1080 burst for an extended period of time due to the bleachers. The rest of the video content provided lacks that repetitious vertical response and appears highly detailed. If it were not for the horizontal lines of the bleachers the rest of the horizontal lines in the stadium test appear slightly thicker than a display or player that does pass. The high resolution of high definition muddies the waters even further compared to standard definition making perception of this anomaly far more difficult for the average viewer.</p>

<p><br />
<b>Keep it Simple</b></p>

<p>For casual viewers it is difficult to be concerned with vertical filtering.</p>

<p>If you aren't sitting close enough to the screen, then you are creating a perceptual filtering effect with your eyes already and vertical filtering concerns play a very small role, if any at all.</p>

<p>For those seeking the immersive experience of close viewing distances following the 3-4 screen heights rule of thumb, you will experience a subtle loss of detail due to vertical filtering. </p>

<p>If you are a performance enthusiast seeking every nuance of detail then vertical filtering is unacceptable for any 1080i source material from DTV to Blu-ray.</p>

<p>Only you can determine the role vertical filtering plays in your system and the content that you watch versus how much money you want to spend to remove the artifact. Please read the <a target="_blank" href="http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php">HQV Benchmark review</a> for more details.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>July 31, 2008 07:23 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1485
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
			
 		<?if (10 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 10
 				AND entry_id <> 1485
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Richard Fisher'
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
 				<h2>About Richard Fisher</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Columns</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_-_vertical_and_horizontal_filtering.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
