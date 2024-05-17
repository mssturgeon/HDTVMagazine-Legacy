<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1368";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1368 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
*/
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# This template is used only for podcasts
#	$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
	$container = 'article_container';
#	$category_page = 'bulletins-category.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="refresh rate, response time, per second, response times, frame rate, screen, rate, response, refresh, time, see, effect, HDTV, color, hdtv, LCD, lcd, often, times, ips, televisions, frames, IPS, DLP, dlp" />
	<meta name="description" content="When you're researching an HDTV, you often find yourself reading what others have posted at various forums such as AVS.  In those forums they tend to use a lot of jargon and lingo that a casual TV watcher may not understand, be aware of, or for that matter even care about.  Today we'll try to define a few of them and add some clarity around the whole subject.  As a &quot;spoiler warning&quot; if you own an HDTV, especially a rear projection HDTV, be warned.  We'll talk about some issues that can appear on the screen that you may have never noticed before.  If you start to look for them, you might start to actually see them.  If you prefer to live in ignorant bliss, you might want to skip this feature." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #271 - HDTV Terms from the Forums</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #271 - HDTV Terms from the Forums'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #271 - HDTV Terms from the Forums</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 28, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_PODCAST) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23271%20-%20HDTV%20Terms%20from%20the%20Forums&amp;bodytext=When%20you%27re%20researching%20an%20HDTV%2C%20you%20often%20find%20yourself%20reading%20what%20others%20have%20posted%20at%20various%20forums%20such%20as%20AVS.%20%20In%20those%20forums%20they%20tend%20to%20use%20a%20lot%20of%20jargon%20and%20lingo%20that%20a%20casual%20TV%20watcher%20may%20not%20understand%2C%20be%20aware%20of%2C%20or%20for%20that%20matter%20even%20care%20about.%20%20Today%20we%27ll%20try%20to%20define%20a%20few%20of%20them%20and%20add%20some%20clarity%20around%20the%20whole%20subject.%20%20As%20a%20%22spoiler%20warning%22%20if%20you%20own%20an%20HDTV%2C%20especially%20a%20rear%20projection%20HDTV%2C%20be%20warned.%20%20We%27ll%20talk%20about%20some%20issues%20that%20can%20appear%20on%20the%20screen%20that%20you%20may%20have%20never%20noticed%20before.%20%20If%20you%20start%20to%20look%20for%20them%2C%20you%20might%20start%20to%20actually%20see%20them.%20%20If%20you%20prefer%20to%20live%20in%20ignorant%20bliss%2C%20you%20might%20want%20to%20skip%20this%20feature.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div style="float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
		</div>
		<div style="clear:right; float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
		<div id="<?=$container?>">
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-29.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
When you're researching an HDTV, you often find yourself reading what others have posted at various forums such as <a target="_blank" href="http://www.avsforum.com/">AVS</a>.  In those forums they tend to use a lot of jargon and lingo that a casual TV watcher may not understand, be aware of, or for that matter even care about.  Today we'll try to define a few of them and add some clarity around the whole subject.  As a "spoiler warning" if you own an HDTV, especially a rear projection HDTV, be warned.  We'll talk about some issues that can appear on the screen that you may have never noticed before.  If you start to look for them, you might start to actually see them.  If you prefer to live in ignorant bliss, you might want to skip this feature.<br />
 <br />
<strong>HDTV Terms from the Forums</strong></p>

<p><strong>Silk Screen Effect, SSE</strong><br />
The Silk Screen Effect, often referred to as simply SSE, applies only to rear projection televisions such as DLP, LCD and LCoS.  Some times, when viewing white or other very bright colored objects, you see what appears to be the texture of the screen itself in front of the image.  This gives the appearance that you're watching the content through a silk screen.  Some also describe it as an unnatural shimmering or sparkling on those bright areas.  It can be greatly reduced with proper calibration.  Typically reducing brightness and contrast, and to some extend adjusting picture control, can nearly eliminate the issue.</p>

<p><strong>Screen Door Effect, SDE</strong><br />
The next acronym on the list is SDE or Screen Door Effect.  This applies to all digital, or fixed pixel, televisions including rear-projection, plasma and flat panel LCD.  If you own one, feel free to investigate this for yourself.  When you get close enough to the screen you can actually see gaps between the pixels, producing what appears to be a grid on the screen.  From that vantage point, it appears as though you're watching TV though a window screen or screen door.  All digital televisions have this issue, but the larger the pixels the more pronounced the effect.  For example the old EDTV (480p) plasmas were infamous for screen door effect, whereas you can only see it on newer 1080p units when you're incredibly (uncomfortably) close to the screen.  The only way to eliminate SDE is to move further away from the screen.</p>

<p><strong>Rainbow Effect</strong><br />
Rainbows are a DLP only phenomenon, specifically single-chip DLP.  They have mostly been eradicated in the newer models, especially the LED based units.  Traditional, bulb-based DLP televisions use a rapidly spinning color wheel to put color on the screen.  The traditional color wheel has red, blue and green segments, and the bulb illuminates the screen in color by shining enough light through each segment that it blends together to form the color you want to see on screen.  As a result only one color is actually on the screen at any given time.  It is possible for some people to see this formation occurring and perceive it as a rainbow of the three distinct colors.  It usually happens when a bright image appears on a very dark background, and for some only happens when they pan their eyes across the screen.  There is no way to reduce the effect in an existing television set.  Manufacturers have eliminated it by using faster color wheels with more color segments.  LED based DLP televisions refresh fast enough that the effect is all but eliminated.</p>

<p><strong>Refresh Rate</strong><br />
A television's refresh rate describes how often a new image can be displayed on screen.  Unlike prior analog technologies (CRT) where the entire screen was redrawn periodically, the new digital TVs only need to update the pixels that have changed since the last time an image was displayed.  So the refresh rate essentially describes how often the display will check to see if any pixels need to be updated.  All HDTV technologies have a refresh rate that should match or exceed the maximum number of video frames that can be shown per second.  As the name implies, it is a rate, witch mathematically is the inverse of time, so the larger number the better.  A refresh rate of 120Hz is better than a refresh rate of 60Hz.</p>

<p><strong>Frame Rate or Frames per Second, FPS</strong><br />
It is important to note the distinction between frame rate and refresh rate.  Frame rate typically describes the video content a television will display.  Again, the higher number the better because the more video frames you get per second, the smoother the motion appears on the display.  So 60 fps is usually considered better than 30 fps, although film is typically shot in 24 fps, so preserving that original rate is often desirable.  A screen must have a refresh rate that equals or exceeds the minimum fps you want to watch.  Obviously if you're trying to view 60 frames per second, but only refreshing the screen 30 times per second, you'll only see every other frame.  Similarly, if the refresh rate of the screen is not a even multiple of the frame rate, the display will need to do some complex math to determine how to show what frames and for how long.  Otherwise some frames will appear for multiple refreshes and others will appear for only one.  This causes really choppy motion on screen.</p>

<p><strong>Response Time</strong><br />
Often confused with refresh rate, response time measures how quickly a display can update an individual pixel.  As a measure of time in this case, the smaller number the better.  We'd like for the response time to be instantaneous, or nearly 0.  Technically, response time is how long it takes for an individual pixel to go from black to white and back to black again.  LCD is the only technology that has ever really suffered from slow response times; Plasma has almost instant response and DLP is very fast as well.  With slow response times it's possible for images, or "shadows" of images to appear on screen longer than they should.  This is often referred to as ghosting or  smearing.  In the early days of LCD TV, a 16 ms response time was deemed adequate for home video, but 12 was necessary for gaming.  Most modern LCD TVs have a response time of 8ms or less, making it almost impossible for most people to see any ghosting.</p>

<p><strong>In-Plane Switching, IPS</strong><br />
Along with slow response times, another known on early LCD televisions was their very narrow viewing angle.  Off angle viewing was, let's just say, less than ideal.  The advent of in-plane switching solved that problem.  The technology itself gets a little too involved to discuss on the show, but it's important to know the LCD TVs with IPS have a practical viewing angle that rivals plasma.  Early versions of IPS caused significant slowdown in response times, as high as 50ms.  A newer version of IPS, called Super In-Plane Switching (S-IPS) offers all the benefit of IPS at the faster response times required by modern HDTV viewers.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 28, 2008 11:23 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1368
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

			<?if (9 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 9
					AND entry_id <> 1368
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'The HT Guys'
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($qry);
				
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					echo '<div class="item"><span class="corners-top"><span></span></span>'.
					'<h2><a href="../../author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
					<h2>About The HT Guys</h2>
					<?=stripslashes($author[bio_short])?>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>
		</td><td id="right">
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
					WHERE entry_blog_id IN (1)
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$res_authors = mQuery($qry);
					while ($row_authors = mysql_fetch_assoc($res_authors)) {
						echo '<li><a href="../../../articles/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_271_-_hdtv_terms_from_the_forums.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
