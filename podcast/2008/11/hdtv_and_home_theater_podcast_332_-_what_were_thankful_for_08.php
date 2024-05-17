<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1565";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1565 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, list things, mass storage, ray discs, home electronics, our, thankful, things, watch, blu, ray, Blu, show, store, time, content, list, Ray, discs, movies, home, netflix, shows, something, new" />
	<meta name="description" content="Each year at this time we stop to give thanks for everything we have in our lives. Home electronics is pretty far down the list in the bigger picture. It goes without saying that we are thankful for our families, our health, our friends, and our wonderful listeners. With that said, there are a few things that we are thankful for in the home electronics area. As is tradition over the last four Thanksgivings, on today's show we give you our list of things we are thankful for." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #332 - What we're thankful for '08</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #332 - What we\'re thankful for \'08'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #332 - What we're thankful for '08</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>November 27, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23332%20-%20What%20we%27re%20thankful%20for%20%2708&amp;bodytext=Each%20year%20at%20this%20time%20we%20stop%20to%20give%20thanks%20for%20everything%20we%20have%20in%20our%20lives.%20Home%20electronics%20is%20pretty%20far%20down%20the%20list%20in%20the%20bigger%20picture.%20It%20goes%20without%20saying%20that%20we%20are%20thankful%20for%20our%20families%2C%20our%20health%2C%20our%20friends%2C%20and%20our%20wonderful%20listeners.%20With%20that%20said%2C%20there%20are%20a%20few%20things%20that%20we%20are%20thankful%20for%20in%20the%20home%20electronics%20area.%20As%20is%20tradition%20over%20the%20last%20four%20Thanksgivings%2C%20on%20today%27s%20show%20we%20give%20you%20our%20list%20of%20things%20we%20are%20thankful%20for.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-11-28.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Each year at this time we stop to give thanks for everything we have in our lives. Home electronics is pretty far down the list in 
the bigger picture. It goes without saying that we are thankful for our families, our health, our friends, and our wonderful listeners. 
With that said, there are a few things that we are thankful for in the home electronics area. As is tradition over the last four 
Thanksgivings, on today's show we give you our list of things we are thankful for.<br><br>
<strong>What We're Thankful For '08</strong><br><br>
<em>Ara:</em>
<br><br>
<strong>Affordable mass storage</strong> - whether you download podcasts, movies, or music you need a place to store everything. In my case, I have a 
video server that houses over 150 movies. Mass storage is essential. Not only do you need to store the content but you need to back 
it up. Today you can buy a <a target="_blank" href="http://www.htguys.com/shop.php?id=B0016P7H3Q">2 TB external hard drive</a> for around $350.
<br><br>
<strong>More HD Content than I can watch</strong> - DirecTV, Dish Network, FiOS, and U-Verse are bringing more HD into our homes everyday! Now we can 
finally chose our HD based on quality programming instead of the fact that its in HD so I have to watch it. And this year Survivor 
finally went HD! Too bad its not what I was hoping for.
<br><br>
<strong>Netflix</strong> - Netflix has put their content on their own player made by <a target="_blank" href="http://www.roku.com/">Roku</a> and recently the Xbox 360. Finding something to watch with 
the service is easy, especially if you like older TV shows. They have begun to add HD content which is a step in the right direction. 
What would make me a lot happier is if they would include 5.1 audio and newer movie releases. Maybe they'll push Apple to make their 
online store an all you can eat proposition?
<br><br>
<strong><a target="_blank" href="http://www.boxee.tv/">Boxee</a></strong> - A very nice Media Center application on its own, but what I like about it is that it turns the AppleTV into what it should be!
<br><br>
<strong>Affordable Blu Ray</strong> - Good Blu Ray discs let your TVs perform the way they were meant to! No compression artifacts and great color. 
That is of course if the discs are mastered properly. Blu Ray discs also have something for the audiophile, lossless audio! As more 
studios start including next generation audio on their discs, our receivers will also have something to cheer about. We hope you picked 
up a great deal on a Blu Ray player on Black Friday!
<br><br>
<em>Braden:</em>
<br><br>
<strong>High Definition Television</strong> - Sounds obvious, right?  After all, this is a show all about HDTV.  Sometimes the biggest and most important 
things are staring you in the face and you just start to take them for granted.  When was the last time you tried to watch a prime time 
drama in standard definition?  Painful huh?  How about a football game?  I can't believe we actually used to watch TV like that and 
enjoy it.
<br><br>
<strong>The Escalating HD Arms Race</strong> - Initially it was DirecTV and Dish Network.  Then Cable realized they couldn't just sit back and hope 
subscribers wouldn't leave.  Throw in the new IPTV players like Verizon and AT&T and it's starting to get interesting.  New HD channels 
are announced daily and most shows we watch are in HD.  Huge things have happened in the last couple years and it doesn't look like 
things are slowing down.
<br><br>
<strong>Downloaded/Streamed Movies</strong> - First there was CinemaNow and MovieLink.  They were good, but maybe a little before their time.  And they 
lacked a "plug and play" solution for the home theater.  Along comes XBox360, Vudu, then AppleTV, the Sony Video store, Netflix and the 
Roku box, and probably a few more we forgot about.  Sure it isn't there yet, but we're getting closer every day.  Vudu HDX is the best 
proof of that to date.
<br><br>
<strong>One HD Disc Format</strong> - Like it or not, Blu-ray won the war this year and HD-DVD is officially a collector's item.  It doesn't matter 
which side of the fence you were on, we can all agree that one format is best.  Less confusion, less fragmentation and hopefully quicker 
adoption.  And the movies are great, too.
<br><br>
<strong>The DVR</strong> - Sure, this is an "old standard" on the list of things we're thankful for, but this wouldn't be an honest list if it wasn't 
included.  We can now try out just about any show on TV for a few episodes to see if we like it, completely hassle free.  Braden's 
family has picked up several new shows this season they probably never would have seen had it not been for the DVR.
<br><br>
And of course we have our listeners to be thankful for. Thank you for your news leads, story ideas, email, and voicemail. Thank you 
for supporting us through our store and for buying us coffee through our Paypal donation link! You all make the show worthwhile! 
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>November 27, 2008 10:37 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1565
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
					AND entry_id <> 1565
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_332_-_what_were_thankful_for_08.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
