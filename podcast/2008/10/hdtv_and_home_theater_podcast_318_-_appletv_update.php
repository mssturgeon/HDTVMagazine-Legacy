<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1510";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1510 AND placement_is_primary = 1";
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
	<meta name="keywords" content="play pause, actor director, hold play, press hold, appletv update, movie, appletv, AppleTV, Apple, apple, play, press, update, add, Play, see, playlists, itunes, Pause, genius, iTunes, shows, Genius, pause, playing" />
	<meta name="description" content="Apple recently released a firmware update for its AppleTV to add support for HD TV Shows. On today's show we talk about the update and our latest experience with the AppleTV. The latest firmware brings the version to 2.2 and, according to Apple, it promises to provide bug fixes, improve stability, and add a feature or two." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #318 - AppleTV Update</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_318_-_appletv_update';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #318 - AppleTV Update'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_318_-_appletv_update.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #318 - AppleTV Update</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October  9, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_318_-_appletv_update.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_318_-_appletv_update.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_318_-_appletv_update.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_318_-_appletv_update.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23318%20-%20AppleTV%20Update&amp;bodytext=Apple%20recently%20released%20a%20firmware%20update%20for%20its%20AppleTV%20to%20add%20support%20for%20HD%20TV%20Shows.%20On%20today%27s%20show%20we%20talk%20about%20the%20update%20and%20our%20latest%20experience%20with%20the%20AppleTV.%20The%20latest%20firmware%20brings%20the%20version%20to%202.2%20and%2C%20according%20to%20Apple%2C%20it%20promises%20to%20provide%20bug%20fixes%2C%20improve%20stability%2C%20and%20add%20a%20feature%20or%20two.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-10.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
<strong>AppleTV Update (<a id="dzmn" href="/shop.php?id=B000RQHAUA" target="_blank" title="160 GB HD">160 GB HD</a> $325,&nbsp;<a id="q2wc" href="/shop.php?id=B000MQNMQ6" target="_blank" title="40 GB HD">40 GB HD</a>&nbsp;$225)</strong>

<div>&nbsp;</div>
<div> Apple recently released a firmware update for its AppleTV to add
support for HD TV Shows. On today's show we talk about the update and
our latest experience with the AppleTV. The latest firmware brings the
version to 2.2 and, according to Apple, it promises to&nbsp;provide
bug&nbsp;fixes, improve stability, and add a feature or two. The following
Table is from Apple:</div>
<div>&nbsp;</div>
<table class="zeroBorder" id="kbtable" style="border-top: 1px solid rgb(161, 165, 169); border-left: 1px solid rgb(161, 165, 169); border-collapse: collapse;" width="100%" border="0" cellspacing="0">
<tbody>
<tr id="header" style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px; background-color: rgb(226, 226, 226);" width="200"><strong><font size="2">Feature</font></strong></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px; background-color: rgb(226, 226, 226);"><strong><font size="2">How To</font></strong></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">HD TV shows</font></td>

<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Purchase the leading HD TV shows directly from the iTunes Store. (US only)</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Movie browsing by actor and director</font></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Browse
movies on the iTunes store by actor and director. Select “More” on the
movie page and browse the actor and director list on the left to see
other movies for that individual.</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Chapter selection, alternate audio and subtitles</font></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">While playing a movie, press and hold&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Play/Pause</strong><font size="1">&nbsp;to
access chapter selection, alternate audio and subtitle display. The
display shows the options available for the current movie.</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Movie description</font></td>

<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Press&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Up</strong><font size="1">&nbsp;when playing a movie to display a description of that movie.</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Genius playlists</font></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">To create a Genius playlist based on the currently playing song, press and hold&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Play/Pause&nbsp;</strong><font size="1">to bring up the contextual menu and select&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Start Genius</strong><font size="1">. Note: The Genius feature must first be enabled in iTunes 8.0.1 and synced with Apple TV.</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">On-The-Go playlists</font></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">To add the currently playing song to the On-The-Go playlist, press and hold&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Play/Pause</strong><font size="1">&nbsp;to bring up the contextual menu and select&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Add To On-The-Go</strong><font size="1">.</font></td></tr>

<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Music videos in playlists</font></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Make
playlists in iTunes combining your favorite music videos and songs.
Play them back on Apple TV and let them play continuously.</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Apple TV standby mode</font></td>
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">From the main menu, press&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Play/Pause</strong><font size="1">&nbsp;for about three seconds (or go to&nbsp;</font><strong style="font-size: 1em; font-style: normal;">Settings</strong><font size="1">&nbsp;&gt;</font><strong style="font-size: 1em; font-style: normal;">Standby</strong><font size="1">).</font></td></tr>
<tr style="background-color: rgb(255, 255, 255);">
<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Security fixes</font></td>

<td style="border-right: 1px solid rgb(161, 165, 169); border-bottom: 1px solid rgb(161, 165, 169); padding: 4px;"><font size="1">Details are available in this&nbsp;</font><a href="http://support.apple.com/kb/HT3189" style="color: rgb(41, 113, 167); text-decoration: none;">article</a><font size="1">.</font></td></tr></tbody></table>
<p style="margin: 0px 0px 18px; padding: 0px;">
    &nbsp;</p>
<p style="margin: 0px 0px 18px; padding: 0px;"><span style="color: rgb(0, 0, 0);">The
latest update rocks! It brings us closer to what the ultimate movie
server should be. With that said there are a few items that we'd like
to see added. Many of these come from an article titled "<a href="http://www.inquisitr.com/4125/what-the-apple-tv-needs/" id="joii" target="_blank" title="What the AppleTV Needs">What the AppleTV Needs</a>" (Thanks to Brad T for the link).</span></p>
<p style="margin: 0px 0px 18px; padding: 0px;"></p>
<ul>
<li>
        Add a DVD or Blu Ray Drive/Player</li>

<li>
Provide a "Back Up" utility that can archive and playback DVDs from a
network drive or an external drive connected via USB </li>
<li>
        Provide Hulu, Netflix, ABC.com, etc right from the GUI, This is unlikely so how about an all you can eat plan.</li>
<li>Support
for the HD Homerun and turn the AppleTV into a DVR. You could attach an
external drive via USB or even record to a NAS device.</li>
<li>AppleTV
App Store - just like the iPhone, there are very creative people out
there that can make this device do some amazing things. We'd like to
see:</li>
<ul>
<li>A weather App</li>
<li>Headlines, Sports, and Stock Ticker</li>
<li>Facebook or MySpace</li>

<li>Games</li></ul></ul>
<div>&nbsp;</div>
<div>Tell us what you'd like to see added <a id="gz3q" href="mailto:hdtvpodcast@mac.com" target="_blank" title="hdtvpodcast@mac.com">hdtvpodcast@mac.com</a>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October  9, 2008 11:19 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1510
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
					AND entry_id <> 1510
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_318_-_appletv_update.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
