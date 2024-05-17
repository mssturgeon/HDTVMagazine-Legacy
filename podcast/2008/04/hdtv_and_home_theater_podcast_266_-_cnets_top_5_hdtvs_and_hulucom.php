<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1336";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1336 AND placement_is_primary = 1";
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
	<meta name="keywords" content="best hdtv, between inches, buy best, hdtv between, best hdtvs, hulu, Hulu, best, HDTV, Best, hdtv, content, video, new, inches, site, buy, shows, show, want, Buy, get, processor, CNet, pretty" />
	<meta name="description" content="For today's show we'll talk about CNet's listing of the 5 best HDTVs they have reviewed.  Then we get into a discussion on Hulu.com - a new video streaming site with a lot of potential.
" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #266 - CNet's top 5 HDTVs and Hulu.com</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #266 - CNet\'s top 5 HDTVs and Hulu.com'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #266 - CNet's top 5 HDTVs and Hulu.com</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 10, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23266%20-%20CNet%27s%20top%205%20HDTVs%20and%20Hulu.com&amp;bodytext=For%20today%27s%20show%20we%27ll%20talk%20about%20CNet%27s%20listing%20of%20the%205%20best%20HDTVs%20they%20have%20reviewed.%20%20Then%20we%20get%20into%20a%20discussion%20on%20Hulu.com%20-%20a%20new%20video%20streaming%20site%20with%20a%20lot%20of%20potential.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-11.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
For today's show we'll talk about CNet's listing of the 5 best HDTVs they have reviewed.  Then we get into a discussion on Hulu.com - a new video streaming site with a lot of potential.</p>

<p>CNet's <a href="http://reviews.cnet.com/4370-6485_7-168-110.html">Best 5 HDTVs we've reviewed</a>:<br />
Best HDTV 32-inches and under<br />
Samsung LN-T3253H (Currently unavailable, get the <a href="http://www.htguys.com/shop.php?id=B00141AYIC">LN-32A450C</a> instead)<br />
 <br />
<strong>Best HDTV between 33- and 42-inches</strong><br />
Panasonic TH-42PZ700U (<a href="http://www.htguys.com/shop.php?id=B000QI1R94">Buy now</a>)<br />
 <br />
<strong>Best HDTV between 43- and 47-inches</strong><br />
Sony KDL-46XBR4 (<a href="http://www.htguys.com/shop.php?id=B000UN8MKM">Buy now</a>)<br />
 <br />
<strong>Best HDTV between 48- and 52-inches</strong><br />
Pioneer PDP-5080HD (<a href="http://www.htguys.com/shop.php?id=B000RPCHSG">Buy now</a>)<br />
 <br />
<strong>Best HDTV 52-inches and up</strong><br />
Panasonic TH-58PZ700U (<a href="http://www.htguys.com/shop.php?id=B000R1G76I">Buy now</a>)</p>

<p> </p>

<p><strong>You gotta check out Hulu.com!</strong></p>

<p>Here's a description from the <a href="http://www.Hulu.com">Hulu.com</a> website:<br />
<em>"Hulu's ambitious and never-ending mission is to help you find and enjoy the world's premium content when, where and how you want it."</em></p>

<p><strong>Background</strong><br />
Hulu.com opened to the public on March 12 of this year.  They provide online video content from over 50 different content providers including Fox, NBC, MGM, Sony Pictures, Warner Brothers, and Lionsgate.  They stream everything, but it isn't just a bunch of video clips and trailers, they have full length television shows and feature films.  The library includes current prime time hits like Hell's Kitchen, Lost, Heroes and Chuck as well as classic shows like Starsky and Hutch, Miami Vice, Diff'rent Strokes and McHale's Navy.  New shows are even made available the morning after they air on network TV.</p>

<p>The movie library is just as impressive.  They've got great movies like the Usual Suspects, Three Amigos!, and the Big Lebowski.  There may not be too many new releases available, but any movie collection that includes Master and Commander: The Far Side of the World, Hercules in New York, and Cheech & Chong's the Corsican Brothers is sure to have something for anyone and everyone.  There's no information on the site on how long it takes for movies to be released.  We'd assume it's sometime after DVD and VOD, but we don't know how long.  In time we expect this delay to get shorter and shorter.</p>

<p>The whole site is ad supported, so it's totally free to viewers.  You can watch whatever you want, as much as you want, whenever you want.  Occasionally you'll see a brief ad before a show or clip starts, then ads will be inserted throughout the video, but not nearly as many as you see on TV.  You can't fast forward through them.  For a 45 minute TV episode we saw 3-4 commercials.  Not a bad deal for the money.</p>

<p><strong>What's the big deal?</strong><br />
First of all, streaming current TV shows on the Internet is pretty cool.  It isn't necessarily new, but it's one site with content from a ton of different sources.  You don't have to go to NBC's website for one show, then jump over to ABC, then to FOX and so on.  Just hook a computer up to the TV and you eliminate the need for a lot of what you pay a Satellite or Cable company for.</p>

<p>But it doesn't stop there.  Hulu has started to play with HD content.  There's an "HD Gallery" on the site that currently holds 20 movie trailers.  According to Eric Feng, chief technology officer of Hulu, each HD clip is encoded in full 720p. We weren't able to analyze it, but for online video it looked and sounded pretty darn good.  If it is 720p, and it's capable of Dolby Digital audio, it might start to make a pretty serious argument for web based television as an alternative to classic broadcast TV.</p>

<p>System Requirements for viewing HD videos at Hulu<ul><li>Flash Player 9.0.115.0</li><li>2.5Mbps Internet connection or greater</li><li>Windows: Intel Pentium 4 3GHz processor (or equivalent), 128MB of RAM, 64MB of VRAM</li><li>Macintosh: Intel Core Duo 1.83GHz or faster processor, 256MB of RAM, 64MB of VRAM</li><li>Linux: Intel Pentium 4 3GHz processor (or equivalent), 128MB of RAM, 64MB of VRAM</li></ul></p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 10, 2008 11:45 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1336
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
					AND entry_id <> 1336
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_266_-_cnets_top_5_hdtvs_and_hulucom.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
