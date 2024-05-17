<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1356";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1356 AND placement_is_primary = 1";
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
	<meta name="keywords" content="give away, get rid, home theaters, home theater, old, new, home, million, give, local, away, maybe, TVs, tvs, buy, nice, HDTV, day, really, sell, even, today, waste, gear, options" />
	<meta name="description" content="In Episode 260 we covered various options for energy efficient home theaters.  One of those involved making sure you look for the Energy Star logo on the products you buy.  After all just because we want to watch HDTV doesn't mean our kids should be robbed of watching Quad-HDTV when they grow up.  There are a lot of colors talked about in home theaters, white noise, black-out shades, red lasers, blu-ray...even rainbows on your DLP TV.  But today we're going to continue in the green theme.  Listener Greg from Michigan thought it would be a good idea to round out the discussion; we've talked about how to buy gear, now we'll cover how to get rid of it." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #269 - How to get rid of your old electronics</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #269 - How to get rid of your old electronics'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #269 - How to get rid of your old electronics</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 21, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23269%20-%20How%20to%20get%20rid%20of%20your%20old%20electronics&amp;bodytext=In%20Episode%20260%20we%20covered%20various%20options%20for%20energy%20efficient%20home%20theaters.%20%20One%20of%20those%20involved%20making%20sure%20you%20look%20for%20the%20Energy%20Star%20logo%20on%20the%20products%20you%20buy.%20%20After%20all%20just%20because%20we%20want%20to%20watch%20HDTV%20doesn%27t%20mean%20our%20kids%20should%20be%20robbed%20of%20watching%20Quad-HDTV%20when%20they%20grow%20up.%20%20There%20are%20a%20lot%20of%20colors%20talked%20about%20in%20home%20theaters%2C%20white%20noise%2C%20black-out%20shades%2C%20red%20lasers%2C%20blu-ray...even%20rainbows%20on%20your%20DLP%20TV.%20%20But%20today%20we%27re%20going%20to%20continue%20in%20the%20green%20theme.%20%20Listener%20Greg%20from%20Michigan%20thought%20it%20would%20be%20a%20good%20idea%20to%20round%20out%20the%20discussion%3B%20we%27ve%20talked%20about%20how%20to%20buy%20gear%2C%20now%20we%27ll%20cover%20how%20to%20get%20rid%20of%20it.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-22.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
In <a target="_blank" href="http://www.htguys.com/archive/2008/March21.html">Episode 260</a> we covered various options for energy efficient home theaters.  One of those involved making sure you look for the Energy Star logo on the products you buy.  After all just because we want to watch HDTV doesn't mean our kids should be robbed of watching Quad-HDTV when they grow up.  There are a lot of colors talked about in home theaters, white noise, black-out shades, red lasers, blu-ray...even rainbows on your DLP TV.  But today we're going to continue in the green theme.  Listener Greg from Michigan thought it would be a good idea to round out the discussion; we've talked about how to buy gear, now we'll cover how to get rid of it.<br />
 <br />
<strong>Even Greener Home Theater</strong></p>

<p>According to a <a target="_blank" href="http://ngm.nationalgeographic.com/2008/01/high-tech-trash/carroll-text">National Geographic</a> article, 25 million televisions are retired each year.  That's in addition to the 30 to 40 million computers that will be thrown out in the next few years.  Of course all of this waste adds up, and fast.  <em>"In the United States, it is estimated that more than 70 percent of discarded computers and monitors, and well over 80 percent of TVs, eventually end up in landfills, despite a growing number of state laws that prohibit dumping of e-waste, which may leak lead, mercury, arsenic, cadmium, beryllium, and other toxics into the ground. Meanwhile, a staggering volume of unused electronic gear sits in storage—about 180 million TVs, desktop PCs, and other components as of 2005, according to the EPA."</em></p>

<p>Sow what do you do when you buy a new TV, or a new DVD player and you simply have no use for the old one?  Maybe the new one pushed the old one into a different room, which pushed another out and so on, until you're left with an ancient hunk of junk that you remember being great in it's day, but it's a real relic now.  As far as we see it, you have three options.</p>

<p><strong>Donate it</strong><br />
Depending on what you have to give, there could be a plethora of options on who would want to take it.  The obvious choices are friends and family.  Maybe someone close to you isn't as addicted to gadgets as you are and even an old relic for you is a huge upgrade for them.  Make their day and give it away.  If you don't have anyone to give it to, look around for a local charity, school or church that might benefit from your hand-me-downs.  And if all else fails, post an ad for free stuff.  You;ll have college kids lined up around that block.</p>

<p><strong>Sell it</strong><br />
Put it up on eBay or Craigslist or in your local classified ads.  Odds are if you can't give it away, it will be tough to sell it.  But maybe it's still worth something, and to help appease the finance committee for the new item, you should sell it instead of giving it away.  A quick glance at eBay shows a ton of really nice electronics equipment that can be had for a great price.  It's often difficult to ship really big TVs, so local ads may work out better.  But brining home $100 toward the $2500 you just put into the new flat screen is a nice gesture.  On second thought, maybe you can use that $100 for a nice dinner and some flowers.  Easier to ask for forgiveness, right?</p>

<p><strong>Recycle it</strong><br />
This is the key.  Whatever you do, don't throw it away.  The eWaste in the ground and on fire around the world is causing untold amounts of pollution in the soil and air.  And it isn't too hard to find a way to recycle.  For example, Sony has an ongoing e-Recycling program called Take Back that will accept your old gear and make sure it's dealt with properly.  In a recent two-day event in the Twin Cities area, Sony, in conjunction with Waste Management, collected over 4 million pounds of unwanted electronics (<a target="_blank" href="http://www.twice.com/article/CA6551398.html">article</a>).  You can read more about it and find a local recycling center <a target="_blank" href="http://www.wm.com/WM/sony/index.asp">online</a>.  Or you can visit <a target="_blank" href="http://www.erecycle.org/">eRecycle.org</a> for a ton of other information and details.</p>

<p>Do your part to ensure that the TVs, DVD players and Receivers we enjoy so much today don't come back to haunt us tomorrow.  OK, so that sounds like the tag line from a really bad B-Movie, but it's something we all need to think about.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 21, 2008 09:57 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1356
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
					AND entry_id <> 1356
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_269_-_how_to_get_rid_of_your_old_electronics.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
