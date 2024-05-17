<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1498";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1498 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dlna servers, dlna server, good dlna, dlna players, play back, DLNA, dlna, server, servers, players, support, video, movie, good, really, any, backup, online, play, Why, cover, network, get, back, why" />
	<meta name="description" content="We've talked about DLNA, or Digital Living Network Alliance, several times before - on Episodes 264 and 161 both get into the nitty gritty on what it is and what it's for.  Let's just cut it short and say that it's a way for consumer electronic devices to share media content.  You've got DLNA servers that dish out content and DLNA players that play it back.  There are a ton of great options out there for DLNA players, as well as a bunch of server options.  While many of the players are top notch, we're having trouble tracking down a really good server.
" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #315 - Why aren't there any good DLNA servers?</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #315 - Why aren\'t there any good DLNA servers?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #315 - Why aren't there any good DLNA servers?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>September 29, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23315%20-%20Why%20aren%27t%20there%20any%20good%20DLNA%20servers%3F&amp;bodytext=We%27ve%20talked%20about%20DLNA%2C%20or%20Digital%20Living%20Network%20Alliance%2C%20several%20times%20before%20-%20on%20Episodes%20264%20and%20161%20both%20get%20into%20the%20nitty%20gritty%20on%20what%20it%20is%20and%20what%20it%27s%20for.%20%20Let%27s%20just%20cut%20it%20short%20and%20say%20that%20it%27s%20a%20way%20for%20consumer%20electronic%20devices%20to%20share%20media%20content.%20%20You%27ve%20got%20DLNA%20servers%20that%20dish%20out%20content%20and%20DLNA%20players%20that%20play%20it%20back.%20%20There%20are%20a%20ton%20of%20great%20options%20out%20there%20for%20DLNA%20players%2C%20as%20well%20as%20a%20bunch%20of%20server%20options.%20%20While%20many%20of%20the%20players%20are%20top%20notch%2C%20we%27re%20having%20trouble%20tracking%20down%20a%20really%20good%20server.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-09-30.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
We've talked about DLNA, or Digital Living Network Alliance, several times before - on Episodes <a title="264" target="_blank" href="/archive/2008/April04.html" id="wzye">264</a> and <a title="161" target="_blank" href="/archive/2007/April10.html" id="f2mx">161</a>
both get into the nitty gritty on what it is and what it's for.&nbsp; Let's
just cut it short and say that it's a way for consumer electronic
devices to share media content.&nbsp; You've got DLNA servers that dish out
content and DLNA players that play it back.&nbsp; There are a ton of great
options out there for DLNA players, as well as a bunch of server
options.&nbsp; While many of the players are top notch, we're having trouble
tracking down a really good server.<strong><br>

<br>Why aren't there any good DLNA servers?</strong><br>
<br>As a side note, DLNA grew
out of the work done by the UPnP Forum (Universal Plug and Play), so
those two terms are often used interchangeably.<br>
<br><strong>DLNA players</strong><br>
<ul>
<li>PS3</li>
<li>Buffalo LinkTheater products</li>
<li>HP MediaSmart TVs</li>
<li>Panasonic Blu-ray players (not all models)<br>
</li>
<li>Hitachi, Philips, Pioneer, Samsung, Sharp and Sony all make TVs with DLNA built in</li></ul><br>

<strong>DLNA Servers</strong><br>
<ul>
<li><a title="Windows Media Player" target="_blank" href="http://www.microsoft.com/windows/windowsmedia/devices/wmconnect/default.aspx" id="u7gh">Windows Media Player</a> </li>
<li><a title="TVersity" target="_blank" href="http://tversity.com/home" id="i1o8">TVersity</a> </li>
<li><a title="PlayOn" target="_blank" href="http://www.themediamall.com/playon" id="kw6:">PlayOn</a> </li>
<li><a title="SimpleCenter" target="_blank" href="http://www.simplecenter.com/" id="qy6y">SimpleCenter</a></li>
<li><a title="Nero MediaHome" target="_blank" href="http://www.nero.com/nero7/eng/Nero_MediaHome.html" id="co70">Nero MediaHome</a> <br>

</li></ul><br>
Here's a nice <a title="table with a bunch of options" target="_blank" href="http://www.rbgrn.net/blog/2007/08/how-to-choose-dlna-media-server-software-in-windows-mac-os-x-or-linux.html" id="ah94">table with a bunch of options</a>.<br>
<strong><br>
What's missing?</strong><br>
So
if you've tried any of the DLNA servers out there, you've probably seen
how bare they are.&nbsp; With so many consumer electronics devices
supporting DLNA playback, it seems a really good DLNA server would sell
like hotcakes.&nbsp; Here's what we think the perfect DLNA server would do.<br>
<br><em>Support Transcoding.</em>&nbsp;
Some DLNA Servers support transcoding, but not all of them.&nbsp;

Transcoding allows the server to change the format of the video your
watching or song you're listening to to something that the player can
actually play back.&nbsp; Otherwise you'll get a bunch of failures trying to
play back stuff like DivX and Xvid.<br>
<br><em>Support Cover Art.</em>&nbsp;
Most DLNA servers will just grab a video frame out of a movie file and
display it as a thumbnail.&nbsp; Why not replace that with the actual cover
of the movie?&nbsp; They're easy to find online.&nbsp; It might take a little
more setup up front to get all the covers downloaded and in the right
place, but it would be worth it.<br>
<br><em>Categorize videos.</em>&nbsp; A
few of the servers support a limited amount of categorization for video
files, but what you'd really like to do is tag a movie with a genre, a
year, rating, actors, director, producer, etc. and use any of those
pieces of information to find the right movie.<br>

<br><em>Built-in DVD backup.</em>&nbsp;
Consumers want a way to backup their movies.&nbsp; DVDs don't last forever.&nbsp;
Allow a user to create a backup copy of their DVD on a hard drive.&nbsp; Of
course they'll also be able to watch it from any network connected DLNA
player, but that's just a slight benefit of the nifty backup feature.&nbsp;
Of course, if you could then provide the built-in...<br>
<br><em>Connection to IMDB.</em>&nbsp;
Allow users to automatically populate cover art and meta data info by
selecting the correct movie from an online database like IMDB.&nbsp; This
would greatly simplify the chore of adding movies to a video library.&nbsp;

While this tends to be a one-time event (once per movie at least), it
can be painful and tends to wear on you after a while.<br>
<br><em>Support online sources.</em>&nbsp;
Right now PlayOn is the only server we've found that supports online
video sources.&nbsp; It, however, doesn't support local sources.&nbsp; It looks
like that functionality will be coming soon, but the two ideas need to
merge for a really great product.<br>
<br>As DLNA becomes more popular
in players, the need for a really good DLNA server will just continue
to grow.&nbsp; Perhaps there's a DLNA server out there that already does all
of this, or maybe most of it.&nbsp; If so, we'd love to hear about it so we
can check it out.&nbsp; Send us an <a href="mailto:hdtvpodcast@mac.com" title="">email</a> if you're using DLNA to serve video
content in your home media network.&nbsp; What server do you use? <br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>September 29, 2008 11:10 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1498
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
					AND entry_id <> 1498
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_315_-_why_arent_there_any_good_dlna_servers.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
