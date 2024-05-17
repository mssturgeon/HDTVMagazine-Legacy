<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1527";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1527 AND placement_is_primary = 1";
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
	<meta name="keywords" content="ansi lumens, lcd projector, overhead projector, total cost, home theater, projector, screen, Projector, LCD, total, project, diy, DIY, lcd, ebay, eBay, lumens, ansi, get, ANSI, cost, native, Screen, Total, build" />
	<meta name="description" content="It's no secret we're into projects; we like to geek out every now and then to see what we can put together without breaking the bank.  We've built our own MythTV server, assembled a Mac based video server and have messed around with whole house audio as well.  Another project we've had in the back of our minds for years now is the infamous DIY Home Theater Projector project.  As we started to do our research, a thought hit us.  Would it be better to build a projector ourselves, or just pay a little extra and buy one on eBay?
" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #321 - DIY or eBay?</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_321_-_diy_or_ebay';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #321 - DIY or eBay?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_321_-_diy_or_ebay.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #321 - DIY or eBay?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October 20, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_321_-_diy_or_ebay.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_321_-_diy_or_ebay.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_321_-_diy_or_ebay.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_321_-_diy_or_ebay.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23321%20-%20DIY%20or%20eBay%3F&amp;bodytext=It%27s%20no%20secret%20we%27re%20into%20projects%3B%20we%20like%20to%20geek%20out%20every%20now%20and%20then%20to%20see%20what%20we%20can%20put%20together%20without%20breaking%20the%20bank.%20%20We%27ve%20built%20our%20own%20MythTV%20server%2C%20assembled%20a%20Mac%20based%20video%20server%20and%20have%20messed%20around%20with%20whole%20house%20audio%20as%20well.%20%20Another%20project%20we%27ve%20had%20in%20the%20back%20of%20our%20minds%20for%20years%20now%20is%20the%20infamous%20DIY%20Home%20Theater%20Projector%20project.%20%20As%20we%20started%20to%20do%20our%20research%2C%20a%20thought%20hit%20us.%20%20Would%20it%20be%20better%20to%20build%20a%20projector%20ourselves%2C%20or%20just%20pay%20a%20little%20extra%20and%20buy%20one%20on%20eBay%3F%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-21.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
It's no secret we're into
projects; we like to geek out every now and then to see what we can put
together without breaking the bank.&nbsp; We've built our own MythTV server,
assembled a Mac based video server and have messed around with whole
house audio as well.&nbsp; Another project we've had in the back of our
minds for years now is the infamous DIY Home Theater Projector
project.&nbsp; As we started to do our research, a thought hit us.&nbsp; Would it
be better to build a projector ourselves, or just pay a little extra
and buy one on eBay?<strong><br>

<br>Do It Yourself or eBay?</strong><br>
<br><strong>The DIY Projector</strong><br>
There's a great article at <a title="inventgeek.com" target="_blank" href="http://www.inventgeek.com/" id="rkan">inventgeek.com</a> called <a title="A Practical Guide to the DIY LCD Projectors" target="_blank" href="http://www.inventgeek.com/Projects/HomeTheater/overview.aspx" id="a555">A Practical Guide to the DIY LCD Projectors</a>.&nbsp;
It lays out, in very simple language the steps required to build your
own projector.&nbsp; One that will come close to HDTV (1024x768) and will
fill a 110" screen.&nbsp; The basic premise is an LCD panel for picture,
mounted to an overhead projector as the lens and light source, inside a
wooden enclosure for sound and light dampening.&nbsp; The idea sounds
great.&nbsp; They project the total cost to be $174 based on these
assumptions:<br>

<br>
<ul>
<li>$17.00: Overhead Projector&nbsp; &nbsp;&nbsp;&nbsp; <br>
</li>
<li>$25.00: LCD Projector Panel &nbsp;&nbsp;&nbsp; <br>
</li>
<li>$110.00: Screen &nbsp;&nbsp;&nbsp; <br>
</li>
<li>$22.00: Construction supplies &nbsp;&nbsp;&nbsp; <br>

</li>
<li><strong>$174.00: Total </strong>&nbsp;&nbsp;&nbsp; <br>
</li></ul><br>
We checked the <a title="HT Guys store" target="_blank" href="http://shop.htguys.com/" id="evm4">HT Guys store</a>
and found the prices to be a bit higher.&nbsp; If you scour eBay, you can
probably get close to what they listed, but we didn't have the patience
for that.&nbsp; Our total was closer to <br>
<br>
<ul>
<li>$89.95: Overhead Projector</li>

<li>$129.00: LCD Monitor (for panel)</li>
<li>$107.00: Screen (<a title="Vutec" target="_blank" href="http://www.htguys.com/shop.php?id=B000PGC9O4" id="t531">Vutec</a>)</li>
<li>$35.00: Construction supplies</li>
<li><strong>$360.95: Total</strong></li></ul><br>
<strong>The DIY Screen</strong><br>
Then, of course, you could make the screen yourself if you really wanted to.&nbsp; <a title="ProjectorCentral.com" target="_blank" href="http://www.projectorcentral.com/" id="ofk0">ProjectorCentral.com</a> has a great article on how to do that called <a title="Make a 100&quot; Screen for under $100" target="_blank" href="http://www.projectorcentral.com/diy_screen.htm" id="k3rn">Make a 100" Screen for under $100</a>.&nbsp;

They not only give excellent detail on how to put the screen together,
they also compare it with a much more expensive screen and give advice
on how to tune your projector to make the DIY screen look its best.&nbsp;
The basic premise with this is very bright white background paper (like
the stuff photographers use for photo shoots) mounted to a wooden frame
that's been wrapped in black velveteen.&nbsp; Total cost is $98.50, saving
you about $10-12 off the total cost of the project.<br>
<br>
<ul>
<li>$26.00: Super-white seamless paper</li>
<li>$42.50: Wood and hardware for frame</li>
<li>$29.00: Velveteen fabric (3 yards) and glue</li>
<li><strong>$98.50: Total </strong></li></ul><br>
<strong>Is it worth it?</strong><br>

So
the screen project really got us thinking.&nbsp; You can spend countless
hours and and intense attention to detail to get a screen that doesn't
perform all that well and only saves you about $10.&nbsp; If the total
project is going to cost $350 or so, what can we put together on eBay?&nbsp;
How much more would it cost to not have to build anything at all?&nbsp; Keep
in mind that the projector is only 1024x768 (although it is 3000
lumens), and the screen is less than stellar on color reproduction and
contrast.&nbsp; It doesn't feel like there's much lower to go on the
projector and screen food chain<br>
<br><strong>Projectors available as of October 16, 2008:</strong><br>
<ul>
<li>NEC LT157 Portable LCD Projector, 1024x768 native resolution, 1500 ANSI Lumens, $249.95</li>

<li>INFOCUS Screenplay 5000, Native 720P resolution, 1100 ANSI Lumens, $26 (needs bulb)</li>
<li>INFOCUS LP280 LCD HDTV Projector, native 800x600, 1024x768 capable, 1000 ANSI lumens, $319</li>
<li>Epson EMP-61 LCD Projector, 800 x 600 native, 1280 x 1024 max resolution, 720p/1080i capable, 2000 ANSI Lumens, $249.99</li>
<li>Sony VPL-PX31 LCD Data Projector, native XGA 1024x768, 2800 ANSI Lumens, $20.50 (works, but appears well worn)</li></ul><br>
Of
course a lot of the concept behind DIY isn't the finished product, it's
the journey you take to get there.&nbsp; We get that.&nbsp; In fact, even
considering the prices of some of those projectors on eBay, we may
still go ahead with the project just to say we've done it.&nbsp; But in that
case, we wouldn't be setting out to build a great home theater
projector, we be setting out to have fun seeing if we could get a DIY
project up and running.&nbsp; Look ma, I made a projector!<br>

<br><br>

		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October 20, 2008 09:55 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1527
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
					AND entry_id <> 1527
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_321_-_diy_or_ebay.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
