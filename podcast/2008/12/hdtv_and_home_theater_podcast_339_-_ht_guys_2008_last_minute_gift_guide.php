<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1584";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1584 AND placement_is_primary = 1";
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
	<meta name="keywords" content="special someone, last minute, home theater, gift suggestions, been busy, gift, new, buy, time, want, give, get, know, someone, little, maybe, special, life, vacuum, jewelry, theater, list, love, Gift, things" />
	<meta name="description" content="It's almost Christmas and maybe you've been too busy watching LCD prices fall, trying to figure out the best time to grab one, and you forgot to pick up a gift for that special someone in your life. Or maybe you're just one of those crazy people who likes to do all their shopping last minute. Whatever your circumstance, we have some tips for you. These are gift suggestions for that special someone in your life. The person who maybe doesn't share your passion for all things Home Theater." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #339 - HT Guys 2008 Last Minute Gift Guide</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #339 - HT Guys 2008 Last Minute Gift Guide'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #339 - HT Guys 2008 Last Minute Gift Guide</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December 22, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23339%20-%20HT%20Guys%202008%20Last%20Minute%20Gift%20Guide&amp;bodytext=It%27s%20almost%20Christmas%20and%20maybe%20you%27ve%20been%20too%20busy%20watching%20LCD%20prices%20fall%2C%20trying%20to%20figure%20out%20the%20best%20time%20to%20grab%20one%2C%20and%20you%20forgot%20to%20pick%20up%20a%20gift%20for%20that%20special%20someone%20in%20your%20life.%20Or%20maybe%20you%27re%20just%20one%20of%20those%20crazy%20people%20who%20likes%20to%20do%20all%20their%20shopping%20last%20minute.%20Whatever%20your%20circumstance%2C%20we%20have%20some%20tips%20for%20you.%20These%20are%20gift%20suggestions%20for%20that%20special%20someone%20in%20your%20life.%20The%20person%20who%20maybe%20doesn%27t%20share%20your%20passion%20for%20all%20things%20Home%20Theater.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-23.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
It's almost Christmas and maybe you've been too busy watching LCD prices fall, trying to figure out the best time to grab one, and you forgot to pick up a gift for that special someone in your life.  Or maybe you're just one of those crazy people who likes to do all their shopping last minute.  Whatever your circumstance, we have some tips for you.  These are gift suggestions for that special someone in your life.  The person who maybe doesn't share your passion for all things Home Theater.
<br><br>
<strong>Last Minute Shopping Guide<br>
<em>For the Special Someone on Any Home Theater Enthusiast's List</em></strong>
<br><br>
<strong>Top Five Gift Suggestions</strong>
<br><br>
<em>5. Gift certificates</em><br>
Lets be honest, for the most part we have no idea what to buy for our wives, girlfriends, significant others, but we do know 
where they like to shop.  You don't have to get in trouble for not knowing what size she wears or what colors she likes.  Give 
her the gift of "freedom" to buy something that she'll love.
<br><br>
<em>4. A vacation</em><br>
Everybody loves to travel.  Book a vacation, even if it's only for a couple days, but plan the whole thing.  Give her a romantic 
trip for two, if you're lucky she'll choose you as the person she decides to take along on the trip.  We know the economy is a 
little tight, but there's got to be a nice little getaway within driving distance.
<br><br>
<em>3. Chores</em><br>
That's right, we said it.  Give the gift of honey-dos.  The list shouldn't include recalibrating the HDTV or setting the proper 
level in your 7.1 speaker system.  If you're anything like us, you wife has a couple things she's been asking you to do that 
you're just been way "too busy" to get to.  Buy her a gift that lets her know you'll get those jobs done.  This can be a tiny 
gift, but if she gets the idea, you'll be a hero.  The tough part will be the follow through.
<br><br>
<em>2. Spa treatments</em><br>
Heck, if you want to send the HT Guys some massage certificates, there's a Burke Williams just down the street...Who doesn't 
need a little "me" time.  After having to put up with listening to you ramble on about 1080p, motion artifacts and discrete vs. 
matrix surround sound, she deserves some time to recharge.  A little goes a long way with this one.
<br><br>
<em>1. Jewelry</em><br>
You can't go wrong with jewelry.  It's the easiest choice in the world.  And with the slow down in the economy, there are some 
really good deals to be had.  You might be the only guy in the store, so they'll be desperate to sell you something.  If you 
buy the right piece of jewelry, you could even give it to her a couple days late and she wouldn't care.
<br><br>
<strong>Top Five Gifts to Avoid</strong>
<br><br>
<em>5. A new "gadget"</em><br>
She knows as well as you do that it's really more for you then it is for her.  Sure, the new Harmony remote will make things 
much easier for her.  She'll love how cool the new iPod touch is.  You aren't fooling anyone ... especially her.  Braden's wife 
has a simple rule: "If you have to plug it in, I don't want it."
<br><br>
<em>4. A Blu-ray player</em><br>
We know a few of you have already justified this in your minds.  You think, "we need to spend more time together, we can do 
that watching movies.  And if we're going to watch movies together, I want them to be the absolute best money can buy; nothing 
but the best for my special someone."  Nice try.  If you already bought it, good luck with that.
<br><br>
<em>3. A toaster</em><br>
OK, so honestly you want to stay away from any kitchen appliance.  This would include a George Foreman grill,  a juicer, you 
name it.  Nothing says "I love you" like "get back in the kitchen and make me a sandwich."  There may be exceptions to this 
rule, like a gourmet coffee maker for the coffee aficionado, but you're on think ice with this one.
<br><br>
<em>2. A gym membership</em><br>
You're taking your life in your own hands with this one.  Don't need to spend too much time here.  Your heart may be in the 
right place, but you'll be resting your head on the couch for many lonely nights with this one.
<br><br>
<em>1. A vacuum</em><br>
To be perfectly honest, you want to avoid anything used for cleaning.  Stay away from Vacuums - even if it is the coolest 
new Dyson that pivots on a ball.   A new mop or an awesome new chamois so she can help you wash your Mustang also fall into 
this category.  Maybe the Roomba vacuum robot, so she doesn't actually have to vacuum.  Then again, refer to #5 in the list.
<br><br>
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December 22, 2008 11:43 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1584
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
					AND entry_id <> 1584
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_339_-_ht_guys_2008_last_minute_gift_guide.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
