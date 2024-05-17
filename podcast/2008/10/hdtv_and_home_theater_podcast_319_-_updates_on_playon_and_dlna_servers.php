<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1512";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1512 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dlna servers, media library, media server, dlna player, nero mediahome, media, DLNA, dlna, PlayOn, playon, netflix, servers, Netflix, server, player, library, Media, get, options, new, actually, our, computer, MediaHome, nero" />
	<meta name="description" content="MediaMall has released a new version of PlayOn that now supports Netflix Watch Instantly streaming.  This is the feature we've all been waiting for, so we had to check it out.  Setup is simple, you just add your Netflix username and password and you're done.  There's even a test button to make sure you typed your password correctly.

For the last couple weeks, since we did Episode #315 complaining about the lack of good choices for DLNA servers, we've received a ton of recommendations and tried out almost a thousand of them.  For the short term we had to focus on software-based options, but there are also a bunch of hardware solutions available as well.  We're trying to get our hands on some of them to add to the trials.  With so many options, something was sure to blow us away, right?" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #319 - Updates on PlayOn and DLNA Servers</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #319 - Updates on PlayOn and DLNA Servers'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #319 - Updates on PlayOn and DLNA Servers</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October 13, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23319%20-%20Updates%20on%20PlayOn%20and%20DLNA%20Servers&amp;bodytext=MediaMall%20has%20released%20a%20new%20version%20of%20PlayOn%20that%20now%20supports%20Netflix%20Watch%20Instantly%20streaming.%20%20This%20is%20the%20feature%20we%27ve%20all%20been%20waiting%20for%2C%20so%20we%20had%20to%20check%20it%20out.%20%20Setup%20is%20simple%2C%20you%20just%20add%20your%20Netflix%20username%20and%20password%20and%20you%27re%20done.%20%20There%27s%20even%20a%20test%20button%20to%20make%20sure%20you%20typed%20your%20password%20correctly.%0A%0AFor%20the%20last%20couple%20weeks%2C%20since%20we%20did%20Episode%20%23315%20complaining%20about%20the%20lack%20of%20good%20choices%20for%20DLNA%20servers%2C%20we%27ve%20received%20a%20ton%20of%20recommendations%20and%20tried%20out%20almost%20a%20thousand%20of%20them.%20%20For%20the%20short%20term%20we%20had%20to%20focus%20on%20software-based%20options%2C%20but%20there%20are%20also%20a%20bunch%20of%20hardware%20solutions%20available%20as%20well.%20%20We%27re%20trying%20to%20get%20our%20hands%20on%20some%20of%20them%20to%20add%20to%20the%20trials.%20%20With%20so%20many%20options%2C%20something%20was%20sure%20to%20blow%20us%20away%2C%20right%3F&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-14.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
<a title="MediaMall" target="_blank" href="http://www.themediamall.com/" id="ma:g">MediaMall</a> has released a new version of <a title="PlayOn" target="_blank" href="http://www.themediamall.com/playon" id="yiwr">PlayOn</a> that now supports <a title="Netflix" target="_blank" href="http://www.netflix.com/" id="u.dt">Netflix</a>

Watch Instantly streaming.&nbsp; This is the feature we've all been waiting
for, so we had to check it out.&nbsp; Setup is simple, you just add your
Netflix username and password and you're done.&nbsp; There's even a test
button to make sure you typed your password correctly.<strong><br>
<br></strong>For the last couple weeks, since we did <a title="Episode #315" target="_blank" href="/archive/2008/September30.html" id="uhbm">Episode #315</a>
complaining about the lack of good choices for DLNA servers, we've
received a ton of recommendations and tried out almost a thousand of
them.&nbsp; For the short term we had to focus on software-based options,
but there are also a bunch of hardware solutions available as well.&nbsp;
We're trying to get our hands on some of them to add to the trials.&nbsp;

With so many options, something was sure to blow us away, right?<br>
<strong><br>
PlayOn Update<br>
</strong><br>
We
installed and tested PlayOn version 2.58.3196 using the PS3 as our
player.&nbsp; The first thing we noticed, even before getting to the Netflix
options, was that they're rearranged how the content is laid out.&nbsp;
Originally each video source (Hulu, YouTube, ESPN, CBS, etc.) showed up
as it's own media server.&nbsp; The PS3 would report PlayOn as multiple DLNA
servers on the network.&nbsp; With the new build, each source shows up as a
sub-item under one PlayOn media server.&nbsp; It's a lot cleaner and
actually makes much more sense.&nbsp; So things are looking good.<br>

<br>Then
we got into the Netflix section of the PlayOn server.&nbsp; What you see is
an alphabetical listing of what's in your Watch Instantly queue at
Netflix.&nbsp; There are way too many titles to try to navigate through them
all on the PS3, so you have to log into Netflix and add them to your
queue in advance.&nbsp; PlayOn shows the cover art, title, a short
description and the playing time for each film.&nbsp; TV Series show up as a
folder with, presumably, each episode listed individually in the
folder.&nbsp; With the couple of series we tried, we weren't able to
actually see any of the episodes listed there.<br>
<br>We wanted to get
a few more titles in the list, so we logged into Netflix and added some
items to the queue.&nbsp; They didn't appear in the PS3 until we forced it
to reload its list of Media Servers.&nbsp; It's unclear whether the problem
was a caching issue with the PS3 or an update problem with PlayOn, but
the workaround was fairly painless.<br>

<br>As to video quality, we were
pleasantly surprised.&nbsp; We watched some older content, like a Peter
Sellers Pink Panther movie and weren't expecting much.&nbsp; The quality
wasn't that bad; very watchable.&nbsp; We had higher hopes for newer films
like Spiderman 3.&nbsp; It looked a little better, and certainly watchable,
but not quite DVD quality.&nbsp; We were surprised that it wasn't in 16:9
format.&nbsp; Because it's streamed and not downloaded, fast forwarding was
unreliable, forcing us to watch the seemingly endless opening credits
for Spiderman, but we digress.<br>
<br>Overall the new Netflix
functionality in PlayOn worked great.&nbsp; It's still in Beta, so we expect
some of the kinks to be worked out, but we were happy with it.&nbsp; Once it
makes it to production, it'll be worth the $30 asking price.<br>

<br><strong>DLNA Server Trials</strong><br>
<br>Bottom
line on DLNA servers, to quote a famous song, we still haven't found
what we're looking for.&nbsp; Ideally we'd get something like <a title="DVDPedia" target="_blank" href="http://www.bruji.com/dvdpedia/" id="ur90">DVDPedia</a>, <a title="MyMovies" target="_blank" href="http://www.mymovies.dk/" id="c2nc">MyMovies</a> or the new <a title="Open Media Library" target="_blank" href="http://www.openmedialibrary.org/" id="q779">Open Media Library</a>
to organize media on the computer, with the ability to stream it out to
a DLNA player on the network.&nbsp; All of those solutions require a
computer at the TV to render the media library.&nbsp; Where's the really
good media library organizer that allows you to tag, organize and
manage you media library, then stream it too?&nbsp; Nowhere we could find.<br>

<br>Because
we're using Braden's PS3 as our test player, all of the options we
looked at were the Windows versions.&nbsp; Those with Mac or Linux versions
may operate differently on the other platforms, but we'd guess they're
pretty close.<br>
<br><strong>What options did we try?</strong><br>
</p>
<ul>
<li><a href="http://www.allegrosoft.com/ams.html" target="_blank">Allegro Media
  Server</a></li>
<li><a href="http://www.cyberlink.com/multi/products/main_111_ENU.html" target="_blank">Cyberlink Digital Home Enabler Kit</a></li>
<li><a title="Google Media Server" target="_blank" href="http://desktop.google.com/plugins/i/mediaserver.html" id="abpv">Google Media Server</a></li>
<li><a href="http://mediatomb.cc/" target="_blank">MediaTomb</a></li>
<li><a title="Nero MediaHome 4" target="_blank" href="http://www.nero.com/enu/mediahome4-introduction.html">Nero MediaHome 4</a></li>

<li><a href="http://www.simplecenter.com/" target="_blank">SimpleCenter Premium</a></li>
<li><a href="http://tversity.com/home" target="_blank">Tversity</a></li>
<li><a href="http://www.twonkyvision.de/Products/TwonkyMedia/index.html" target="_blank">TwonkyMedia</a></li>
<li><a title="Windows Media Player 11" target="_blank" href="http://www.microsoft.com/windows/windowsmedia/player/11/default.aspx">Windows Media Player 11</a></li></ul><strong>Which ones are worth a look?</strong><br>
<br><a title="Nero MediaHome 4" target="_blank" href="http://www.nero.com/enu/mediahome4-introduction.html" id="vgyj">Nero MediaHome 4</a>
($39.99) Wins for easiest overall DLNA server.&nbsp; It is simple to setup,
simple to use, and just works.&nbsp; The interface isn't very flashy, but it
is functional.&nbsp; The tree control used to view the media library doesn't
let you do anything to organize your collection, and it seemed to
falter with really large collections of media files.&nbsp; Bottom line, not
a lot of features, but it works like a tank.<br>

<br><a title="Windows Media Player 11" target="_blank" href="http://www.microsoft.com/windows/windowsmedia/player/11/default.aspx" id="az40">Windows Media Player 11</a>
(Free) Wins for best media organizer.&nbsp; The library organization
features of WMP11 are the best out there.&nbsp; You can set all sorts of
different attributes on files, but not all of those actually show up
when you stream the media to a DLNA player.&nbsp; So while they're cool on
the PC, they're of limited use on the DLNA side.&nbsp; One cool feature is
the ability to limit what kinds of content each DLNA player is allowed
to get, by rating for example.&nbsp; <br>
<br><a title="TVersity" target="_blank" href="http://tversity.com/home" id="i7se">TVersity</a>
(Free) Wins for best transcoder.&nbsp; The media organization is limited,
and because the UI is built to be a little flashy, it's actually a bit
more cumbersome than Nero MediaHome.&nbsp; But on the plus side, using
TVersity, the PS3 was able to render media files that most of the other
servers couldn't.&nbsp; A couple of the other media servers handled them,
but their UI on the computer was so bad they didn't make the cut.<br>

<br>Now to uninstall about a thousand DLNA servers from our test machine.&nbsp; We really need to get VMs for this stuff...<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October 13, 2008 11:40 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1512
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
					AND entry_id <> 1512
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_319_-_updates_on_playon_and_dlna_servers.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
