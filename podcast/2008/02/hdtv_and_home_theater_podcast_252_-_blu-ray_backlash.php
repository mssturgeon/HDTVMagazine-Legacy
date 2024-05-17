<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1270";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1270 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, dvd player, upconverting dvd, high definition, ray player, blu, ray, Blu, DVD, dvd, player, why, war, our, backlash, much, isn, good, movies, high, upconverting, content, little, format, better" />
	<meta name="description" content="We had planned some very different content for today's show, but something very strange happened this week.  There seems to be a huge backlash against Blu-ray for some reason.  It caught us somewhat by surprise, but the trend is too strong to ignore, so we just have to talk about it.  If the war really is over, and by most accounts it is, why aren't people rejoicing in the peaceful times ahead?  Why can't we put the differences behind us and move on to soak in all our movies in HD on one kind of disc, and maybe enjoy a little wide scale adoption at the same time?" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #252 - Blu-ray Backlash</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #252 - Blu-ray Backlash'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #252 - Blu-ray Backlash</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>February 22, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23252%20-%20Blu-ray%20Backlash&amp;bodytext=We%20had%20planned%20some%20very%20different%20content%20for%20today%27s%20show%2C%20but%20something%20very%20strange%20happened%20this%20week.%20%20There%20seems%20to%20be%20a%20huge%20backlash%20against%20Blu-ray%20for%20some%20reason.%20%20It%20caught%20us%20somewhat%20by%20surprise%2C%20but%20the%20trend%20is%20too%20strong%20to%20ignore%2C%20so%20we%20just%20have%20to%20talk%20about%20it.%20%20If%20the%20war%20really%20is%20over%2C%20and%20by%20most%20accounts%20it%20is%2C%20why%20aren%27t%20people%20rejoicing%20in%20the%20peaceful%20times%20ahead%3F%20%20Why%20can%27t%20we%20put%20the%20differences%20behind%20us%20and%20move%20on%20to%20soak%20in%20all%20our%20movies%20in%20HD%20on%20one%20kind%20of%20disc%2C%20and%20maybe%20enjoy%20a%20little%20wide%20scale%20adoption%20at%20the%20same%20time%3F&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<strong>Today's Show:</strong><br>
<br>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-22.mp3">Listen Now - mp3</a>
<br>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<br>
<a href="http://www.htguys.com">Website</a>
<br>
<br>
We had planned some very different content for today's show, but something very strange happened this week.  There seems to be a huge backlash against Blu-ray for some reason.  It caught us somewhat by surprise, but the trend is too strong to ignore, so we just have to talk about it.  If the war really is over, and by most accounts it is, why aren't people rejoicing in the peaceful times ahead?  Why can't we put the differences behind us and move on to soak in all our movies in HD on one kind of disc, and maybe enjoy a little wide scale adoption at the same time?<br><br>
<div><strong>Blu-ray Backlash</strong><br>
<br>
 Let's look back for a second.  When the format war began, we chose to remain neutral - even we we thought that Blu-ray did, in fact, look better at the CES 2005 show.  We didn't even report that, to keep our own biases out of the debate.  As part of that neutral stance we tried to hold out on buying a player until there was a clear winner.  As time went on the high definition junkie in both of us did us in.  Ara picked up an HD-DVD add on drive for his Xbox360 because it was very cost effective.  Braden purchased a Toshiba HD-A2 and took the umpteen free movies that came with it to sweeten the deal.  For only $299, at the time it seemed like a small add to get a really good upconverting DVD player that also played high def discs.<br>
<br>It was hard to remain neutral, but our stance stood firm that the Toshiba was a good deal as an upconverting DVD player that also played HD-DVDs, so in the event that HD-DVD lost the war, you weren't out the whole investment.  Of course, eventually Ara picked up a dedicated Blu-ray player and Braden recently acquired a PS3 (more on that in a future episode), and we moved firmly back into the neutral position.  All along it was our belief that content would decide the format war.  When Warner made the move to Blu-ray only, we had a pretty good idea that the end was near.  And it looks like the Blu-ray camp's assertion at CES 2007 that "the end is here" may not have been as bold as we thought it was.  Perhaps they knew a little more than they let on.<br>
<br>But with the war behind us, we should all be ready to move on, right?  There might be some of us who feel a little bit of sadness with the passing of HD-DVD.  Braden wasn't shy about proclaiming how much he liked the format and thought that it was executed very well.  Others might be a little bit upset about the HD-DVD player purchase they made that can now serve as a giant paperweight, which is understandable.  Of course it's still a good upconverting DVD player, but that's not much consolation right now.  So after we wipe our tears and lick our wounds, we can see that this war, this thing everyone from retailers to the media railed against, is finally over.  All the confusion, the doubt, all gone.  But for some reason there are those who still just hate Blu-ray.<br>
<br>
For example, several emailers have sent in a few different blogs about why Blu-ray still isn't any good.  One post dated Feb. 13, 2008 at <a title="ZDNet.com" target="_blank" href="http://www.zdnet.com/">ZDNet.com</a> entitled <a title="Is Blu-ray worth it?" target="_blank" href="http://blogs.zdnet.com/storage/?p=289" id="xpcn"><em>Is Blu-ray worth it?</em></a> lists the pros and cons of a Blu-ray player and concludes that, in fact, no, the Blu-ray player isn't worth it.  If you're a fan of high definition movies like we are, how can it not be worth it?  He argues that the players are flaky, they don't all support everything you'd want them too like audio codecs or Internet connectivity, and the content just isn't that much better than an upconverted DVD.  While it's true that Blu-ray's market execution wasn't stellar, the content is much better.  It isn't quite the leap we made from VHS to DVD or from standard def TV to HDTV, but it's certainly better.  If you have an HDTV, it's worth it to watch movies in high definition.<br>
<br>Another post at <a title="USNews.com" target="_blank" href="http://www.usnews.com/" id="ggnd">USNews.com</a> date Feb. 20, 2008 and titled <a title="7 Reasons to Forget Blu-ray" target="_blank" href="http://www.usnews.com/blogs/daves-download/2008/2/20/7-reasons-to-forget-blu-ray.html?msg=1" id="gxv7">7 Reasons to Forget Blu-ray</a> provides reason why consumers should just skip Blu-ray altogether.  The reasons can be summed up as: 1. upconverting DVD players are close enough, or 2. you can get HD movies elsewhere, such as Vudu or AppleTV.  All decent arguments, of course the quality isn't comparable, but the convenience factor is very high.  The real question is "why now?"  Why when Blu-ray has just won the war, we finally have one format we can get behind, is there so much backlash.  We can understand backlash against dueling formats, that's just plain confusing to customers.  But it's over now.  There's one message to sell to the masses.  Why hack it down now?<br>
<br>
We'll close with the <a title="Top 11 Ways Sony Plans to Celebrate Their Blu-Ray Victory" target="_blank" href="http://www.bbspot.com/News/2008/02/top-11-ways-sony-plans-to-celebrate-their-blu-ray-victory.html" id="oc.j">Top 11 Ways Sony Plans to Celebrate Their Blu-Ray Victory</a> from <a title="BBSpot.com" target="_blank" href="http://www.bbspot.com/" id="mpgz">BBSpot.com</a>.<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>February 22, 2008 06:26 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1270
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
					AND entry_id <> 1270
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_252_-_blu-ray_backlash.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
