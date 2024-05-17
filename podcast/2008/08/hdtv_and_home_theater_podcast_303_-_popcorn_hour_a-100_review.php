<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1505";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1505 AND placement_is_primary = 1";
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
	<meta name="keywords" content="popcorn hour, media player, networked media, media tank, looked good, Hour, Popcorn, hour, popcorn, play, network, get, media, content, review, support, back, want, any, USB, Networked, hdmi, formats, video, usb" />
	<meta name="description" content="Not a week goes by that we don't hear something about the Popcorn Hour A-100 network media player.  We tried relentlessly to get a demo unit for review, but to no avail.  Finally, slightly weary but committed whole-heartedly to the show, Ara decided to pony up the cash and purchase one.  It arrived last week and we got a chance to play with it.  All in all, not a bad little unit." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #303 - Popcorn Hour A-100 Review</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #303 - Popcorn Hour A-100 Review'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #303 - Popcorn Hour A-100 Review</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>August 20, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23303%20-%20Popcorn%20Hour%20A-100%20Review&amp;bodytext=Not%20a%20week%20goes%20by%20that%20we%20don%27t%20hear%20something%20about%20the%20Popcorn%20Hour%20A-100%20network%20media%20player.%20%20We%20tried%20relentlessly%20to%20get%20a%20demo%20unit%20for%20review%2C%20but%20to%20no%20avail.%20%20Finally%2C%20slightly%20weary%20but%20committed%20whole-heartedly%20to%20the%20show%2C%20Ara%20decided%20to%20pony%20up%20the%20cash%20and%20purchase%20one.%20%20It%20arrived%20last%20week%20and%20we%20got%20a%20chance%20to%20play%20with%20it.%20%20All%20in%20all%2C%20not%20a%20bad%20little%20unit.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-08-19.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Not
a week goes by that we don't hear something about the Popcorn Hour
A-100 network media player.&nbsp; We tried relentlessly to get a demo unit
for review, but to no avail.&nbsp; Finally, slightly weary but committed
whole-heartedly to the show, Ara decided to pony up the cash and
purchase one.&nbsp; It arrived last week and we got a chance to play with
it.&nbsp; All in all, not a bad little unit.<br>
<strong><br>
Popcorn Hour A-100 Review</strong><br>
<a title="Popcorn Hour" target="_blank" href="http://www.popcornhour.com/onlinestore/index.php?pluginoption=catalog&amp;task=info&amp;item_id=5&amp;main_id=0&amp;category_id=">Popcorn Hour A-100, $179</a> <br>

<br>It appears as though the name 'Popcorn Hour' is the commercialized product version of a device made by <a title="Syabas" target="_blank" href="http://www.syabas.com/main.html">Syabas</a> otherwise known as the Networked Media Tank, or NMT
for short.&nbsp; We'll get into more detail as the review goes on, but to
skip straight to the bottom line, 'Networked Media Tank' is an
excellent name for this product.&nbsp; There's not much about it that's sexy
or flashy, but it works and it works well.&nbsp; Nobody in their right mind
would call it the Networked Media Ferrari, but that doesn't mean you
wouldn't want to buy one.<br>
<br>To get back to
the beginning, the Popcorn Hour is a small media player that allows you
to playback just about any digital multimedia file.&nbsp; It supports every
file type we tried to play, from pictures to music to movies.&nbsp;
Reportedly there are some formats it won't play, and of course it has
trouble withDRM protected content, but they also have the ability to
add new codecs and formats with firmware upgrades, so if there's
something it won't do, you can bet they're working on adding it.<br>
<br>For playback you have composite video, s-video, component and HDMI on the video side and stereo and digital coax on the audio side.&nbsp; The high def outputs (component and HDMI)
support every format from 480i to 1080p/60, and even include support
for the 50 fps formats like 720p/50 and 1080p/50.&nbsp; You have three
options on where you can get the content you want to play back.&nbsp; The
easiest option is to just plug any USB storage device into the front of
it.&nbsp; Then you just play it.&nbsp; You can also add a hard drive to the unit
(not included, but the hardware needed to install it is) to store files
locally on the Popcorn Hour itself.&nbsp; And last but not least the most
popular option is probably the ability to play content from any network
connected computer or storage device.&nbsp; Right now it only supports hard
wired connections, but support for wireless USB adapter dongles could be added in the future via firmware upgrade. <br>
<br>As
we already alluded to, the user interface isn't anything that will make
you do back flips in the living room.&nbsp; While it is better than other
similar devices we've used, it doesn't hold a candle to theAppleTV or a
PS3.&nbsp; It would be difficult to imagine how it could, when you look at
the relative budgets that must have gone into each of those products.&nbsp;

No while it isn't sleek or sexy, it is very functional and intuitive.&nbsp;
If you know how your folders are arranged, you can find anything you
need pretty quickly.&nbsp; Because it's all folder based, there are some
limitations.&nbsp; You don't get cover art or descriptions for files, even
if they have that information in themetadata .&nbsp; There is no searching
or sorting or any kind of advanced content management features.&nbsp; There
are also some other weird side effect like: you can play all songs in a
folder, or just one song, but you can't start an album from the middle
unless you create aplaylist for it. <br>
<br>If
you want an inexpensive network media player that can play anything,
the Popcorn Hour is the best we've seen so far.&nbsp; It requires some IT
expertise to get it up and running and to use it on a day-to-day basis,
but it works like a champ.&nbsp; Even streaming 1080p HD content over the
network looked good, granted it was all that was happening on the
network at the time, but it still looked good.&nbsp; If you want something
with a little more visual appeal, stick withAppleTV, the PS3 or the Xbox
360 (or Vudu when it adds local network playback).&nbsp; But all of those
options are a bit more expensive and not quite as flexible.<br>
<br>While we tested the A-100 a new Popcorn Hour, the <a title="A-110" target="_blank" href="http://www.popcornhour.com/onlinestore/index.php?pluginoption=catalog&amp;task=info&amp;item_id=6&amp;main_id=0&amp;category_id=">A-110</a> is now available for pre-order.&nbsp; The A-110 adds SATA HDD and USB Slave functionality and support for HDMI 1.3a including HD Audio pass-through for DTS HD-HR, DTS HD-MA, Dolby Digital Plus and Dolby TrueHD.&nbsp; That new functionality will set you back an additional $35 - it retails for $215.<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>August 20, 2008 06:51 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1505
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
					AND entry_id <> 1505
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_303_-_popcorn_hour_a-100_review.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
