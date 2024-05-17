<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1547";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1547 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dish network, appletv vudu, demand system, free pay, available download, demand, movie, directv, download, content, DirecTV, movies, dish, Dish, watch, available, Demand, free, system, network, isn, get, even, navigate, DVR" />
	<meta name="description" content="Lately when we have been talking about VOD we have been talking abut AppleTV or Vudu. After going to CEDIA last week and visiting with DirecTV and Dish Network we decided to see what improvements both companies have implemented in their respective products. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #310 - DirecTV and Dish Network Video On Demand</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #310 - DirecTV and Dish Network Video On Demand'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #310 - DirecTV and Dish Network Video On Demand</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>September 12, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23310%20-%20DirecTV%20and%20Dish%20Network%20Video%20On%20Demand&amp;bodytext=Lately%20when%20we%20have%20been%20talking%20about%20VOD%20we%20have%20been%20talking%20abut%20AppleTV%20or%20Vudu.%20After%20going%20to%20CEDIA%20last%20week%20and%20visiting%20with%20DirecTV%20and%20Dish%20Network%20we%20decided%20to%20see%20what%20improvements%20both%20companies%20have%20implemented%20in%20their%20respective%20products.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-09-12.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Lately when we have been talking about VOD we have been talking abut AppleTV or Vudu. After going to CEDIA last week and visiting with DirecTV and Dish Network we decided to see what improvements both companies have implemented in their respective products. 
<br><br>
<strong>DirecTV</strong><br>
Before we get started in order to use the service with DirecTV you must have:

<ul><li>A DIRECTV Plus® HD DVR receiver</li>
<li>Broadband Internet service with a minimum connection speed of 750 Kbps or higher (DSL or cable)</li>
<li>Ethernet connect to the DVR</li></ul>


The DirecTV GUI is easy to navigate but not as nice as the AppleTV or Vudu. Selecting movies is similar to navigating folders. The categories are down the left side and movies in each category on the rest of the screen. You can choose content by genre or channel. There are about a hundred channels of on demand content when you include SD material. DirecTV claims to have over 4500 movies, TV series, kids’ shows, specialty programs, music videos and more in both SD and HD.
<br><br>
The first movie up was a Standard Definition version of American Pie. It was wide screen but within the 4:3 window. The movie came with a brief advertisement for Showtime on demand. The movie was watchable within three minutes but would stop because not enough of it was downloaded. I wasn't patient enough to find out how long you needed for a Standard Def movie to buffer before it was watchable without interruption but it was more than 10 minutes. Quality was similar to SD content on DirecTV.
<br><br>
Then it was on to HD. There is a selection of free and for pay movies. I selected Rocky Balboa which was free from Showtime on demand. I was able to watch it within a few minutes but like the similarly to the SD movie it stopped playing in about a minute.  The picture quality was what we have been calling DVD+. The guide said that the audio was 5.1 but I was only able to get pro logic out of it. In fact none of the on demand content that I downloaded that was tagged as 5.1 actually played in 5.1. 
<br><br>
HD is not on demand. I would call it kind of on demand. To make the best use out of it I would recommend deciding on a movie a couple of hours before you want to watch it and start the download. 
<br><br>
The movie selection is pretty good with a mix of new and old movies ranging in price from free to $4.99. The pay movies have a 24 hour rental period once you start watching the movie. DirecTV has about 75 HD movies and content from Showtime, Smithsonian, and the 101 (a DirecTV channel). 
<br><br>
Each free on demand recording comes with a commercials that you can skip through. Not everything on a given channel is available for download. For instance I wanted to watch Emeril Live but unfortunately it was not available. Throwdown with Bobby Flay was available so I tried to download an episode as I was watching another on demand title which was currently downloading. Unfortunately you can't do that. One download at a time regardless how fast your connection is. Once I stopped the current download, Bobby Flay started showing up on my DVR.
<br><br>
A nice feature that this on demand system has is that you can select a feature and have it download from the Internet. You can use any computer of even your iPhone. Its actually easier to see what's available using your computer. I selected a few music Videos in HD on my lunch hour and had them waiting for me when I got home later that evening.
<br><br>
Overall On Demand is nice addition to the HD DVR. In a pinch you can find an HD movie and there is plenty of SD content to watch. But if you do download 1080p, plan on downloading it the day before.
<br><br>
<strong>Dish Network</strong><br>
Dish, on the other hand, isn't quite so easy to navigate.  There are three "on demand" options.  The first is HD Pay Per View, which really isn't On Demand, but it's close so we count it.  The second is the standard On Demand downloads via the Satellite network and the third option, Dish Online, offers broadband download.  Obviously the PPV offering is easy to use.  It's just like any other PPV that you've ever seen, so we'll focus on the other two.
<br><br>
Most of the content is available using the On Demand system.  It isn't the easiest thing in the world to navigate.  There's a giant list of movies or TV shows to scroll through to find what you're looking for.  Some cost money, some are free, and there's a little dollar sign icon letting you know which is which.  They don't have an HD icon in this system, which leads us to believe that there isn't any HD content there yet.  We sure couldn't find any.
<br><br>
The interface does let you filter the list by categories such as movie rating, genre and release year.  Or, if you know what you're looking for you can search for it by name, actor name, etc.  In general the interface is not quite up to par with other offerings.  Even the Playstation Video Store does a better job.
<br><br>
You can download multiple programs at the same time and even watch one while others are downloading.  That barely makes up for how slow the system responds to even the smallest interaction.
<br><br>
The Dish Online offering, however, is much, much easier to navigate.  This is the side that requires a broadband connection.  Setup is simple if you're using DHCP, and the UI is pretty snappy.  Perhaps that is due to the fact that only 10 movies are available for download.  All are listed as HD and all of them cost $6.99 to rent.  Not a ridiculous price, but not great either.  They look fine after you get them to the box, but certainly not Blu-ray quality.
<br><br>
Dish looks like they're moving in the right direction, trying to provide as many options for content as they can, they just haven't quite arrived yet.  Sure in a pinch you could get a movie or TV show to watch, but we won't be getting rid of our AppleTV or Vudu boxes just yet.<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>September 12, 2008 12:07 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1547
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
					AND entry_id <> 1547
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_310_-_directv_and_dish_network_video_on_demand.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
