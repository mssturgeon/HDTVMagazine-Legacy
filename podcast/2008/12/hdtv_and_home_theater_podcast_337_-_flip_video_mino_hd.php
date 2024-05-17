<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1578";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1578 AND placement_is_primary = 1";
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
	<meta name="keywords" content="flip video, video mino, composite video, video clips, direct video, video, mino, Mino, Video, clips, camcorder, get, flip, enough, Flip, videos, email, image, record, cool, recorded, back, source, good, really" />
	<meta name="description" content="Just when you might think that you have enough gadgets, you come across a new one that reminds you: you can never have enough cool stuff. We were lucky enough to get our hands on the &quot;world's smallest HD camcorder,&quot; the Flip Video Mino HD. Not only was it a lot of fun to play with, it was very useful and extremely convenient. If you'd like to record some Holiday memories this year in true 720p high definition, or you need to find a cool, unique gift for the techie who has everything, the Mino HD is a sure hit. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #337 - Flip Video Mino HD</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #337 - Flip Video Mino HD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #337 - Flip Video Mino HD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December 16, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23337%20-%20Flip%20Video%20Mino%20HD&amp;bodytext=Just%20when%20you%20might%20think%20that%20you%20have%20enough%20gadgets%2C%20you%20come%20across%20a%20new%20one%20that%20reminds%20you%3A%20you%20can%20never%20have%20enough%20cool%20stuff.%20We%20were%20lucky%20enough%20to%20get%20our%20hands%20on%20the%20%22world%27s%20smallest%20HD%20camcorder%2C%22%20the%20Flip%20Video%20Mino%20HD.%20Not%20only%20was%20it%20a%20lot%20of%20fun%20to%20play%20with%2C%20it%20was%20very%20useful%20and%20extremely%20convenient.%20If%20you%27d%20like%20to%20record%20some%20Holiday%20memories%20this%20year%20in%20true%20720p%20high%20definition%2C%20or%20you%20need%20to%20find%20a%20cool%2C%20unique%20gift%20for%20the%20techie%20who%20has%20everything%2C%20the%20Mino%20HD%20is%20a%20sure%20hit.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<!--HDTV and Home Theater Podcast #-->
<div align="center" style="height:55px; padding-top:20px">
<span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span>
<span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-16.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Just when you might think that you have enough gadgets, you come across a new one that reminds you: you can never have 
enough cool stuff.  We were lucky enough to get our hands on the "world's smallest HD camcorder," the Flip Video Mino HD.  
Not only was it a lot of fun to play with, it was very useful and extremely convenient.  If you'd like to record some 
Holiday memories this year in true 720p high definition, or you need to find a cool, unique gift for the techie who has 
everything, the Mino HD is a sure hit.
<br><br>
<a target="_blank" href="http://www.theflip.com/products_flip_mino.shtml">Flip Video Mino HD</a><br>
(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001HSOFI2">Buy at the HT Guys Store, $206</a>)
<br><br>
The first thing you notice when you open the Mino HD is how small it is.  It measures 3.94" x 1.97" x 0.63" (10 x 5 x 1.6 cm) 
and weighs only 3.3 ounces.  Easily smaller than an iPod or PDA, in fact smaller than most cell phones and digital still cameras, 
the Mino HD is incredibly portable.  Also included in the box are a wrist strap, a carry case and an a/v cable (composite video 
and stereo audio.
<br><br>
Using the Mino is a snap.  If recharges via USB, so you plug it into your computer for three hours and it's all set to go.  
When you're ready to capture some video you simply turn it on and hit the big red record button.  While recording you can 
zoom in and zoom out.  After recording a clip, you can watch it back with full volume control and even delete it immediately 
if it didn't turn out like you'd hoped.
<br><br>
When you're done shooting all the video you need, all you have to do is plug it back into the computer and start working 
with the clips.  The Mino HD includes a simple video application right on the camera, so you can watch, save, edit, email, 
post to the web - just about anything - without installing a single program.  The application, called FlipShare, is very 
basic, but it does enough to allow a novice user to do the essentials.  It runs on both Windows and Mac OS, so everyone 
should be happy.
<br><br>
<em>FlipShare features:</em>
<ul>
<li>Video and save clips recorded on the Mino HD</li>
<li>Cut pieces of clips out with a very simple interface</li>
<li>Tie videos together to make movies, including optional background music</li>
<li>Email video clips to friends and family</li>
<li>Email video greeting cards</li>
<li>Post videos directly to the Internet, with support for AOL Video, myspace and YouTube</li>
<li>Prepare videos to be burned to DVD</li>
</ul><br>
The Mino HD has 4 GB of built in memory that can record up to 60 minutes of footage.  The memory isn't expandable, so you 
need to get everything recorded in an hour.  It records in 720p, 1280 x 720, 16:9 widescreen at 30 fps.  The video actually 
looks really good.  It is stored as an MPEG4 file using H.264 and AAC audio.  The camera doesn't have image stabilization, 
so you have to stay pretty still if you want the image to look as good as possible.  The audio sounds good when you're 
close to the sound source or if the source is really loud.  The further away the source is, the worse it becomes.
<br><br>
When we briefly mentioned the Mino HD on <a target="_blank" href="http://www.htguys.com/archive/2008/December09.html">Episode 335</a> 
and reported that it didn't have direct video output capabilities.  We were wrong.  It does have direct video output, but 
only in standard definition.  You can plug the composite video cable directly into the Mino HD and play your videos back 
on any TV.  The quality will be better if you burn them to DVD, though.
<br><br>
Our Mino HD happens to be black, and we think it's pretty cool looking.  As a consumer, though, if you get yours through the 
<a target="_blank" href="http://www.theflip.com/store/">Flip Video Store</a>, 
you can completely customize the look of the camcorder.  You can choose from pre-designed patterns and images from 
the design gallery, upload your own image and even use a pattern generator to create your own, custom, one of a kind 
Mino HD that shows your unique personality.  The custom designs don't cost any more than the standard black one we have.
<br><br>
Overall the Mino HD is awesome.  While it won't replace a true camcorder, and it isn't going to get you into the 
wedding video business, it is extremely portable and very easy to use.  Imagine how many more video clips you would 
have and how many more memories would be preserved in high definition if your camcorder fit in your pocket easier than 
your cell phone.  It's so easy to take with you, the possibilities just completely unfold.  In the short time we had 
it, we took video of events we never would have recorded before because of the hassle associated with it.  The Mino 
really is is hassle-free HD.
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December 16, 2008 12:27 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1578
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
					AND entry_id <> 1578
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
					FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
					WHERE
						t.forum_id = af.forum_id
						AND af.exclude_general = 0
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_337_-_flip_video_mino_hd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
