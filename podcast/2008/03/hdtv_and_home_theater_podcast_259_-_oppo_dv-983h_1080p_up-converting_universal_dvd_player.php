<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1299";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1299 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dvd player, oppo digital, oppo players, press setup, region free, player, Oppo, oppo, DVD, dvd, video, audio, well, digital, Digital, players, press, DVDs, performance, converting, make, Press, our, high, dvds" />
	<meta name="description" content="Well the folks at Oppo Digital are at it again. This time they have released a new high end Up-Converting DVD player. The DV-983H is the new gold standard in up-converting DVD players. It incorporates Video Reference Series processing (VRS by) Anchor Bay. This is the same technology used in video processors costing $3000 or more." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #259 - Oppo DV-983H 1080p Up-Converting Universal DVD Player</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #259 - Oppo DV-983H 1080p Up-Converting Universal DVD Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #259 - Oppo DV-983H 1080p Up-Converting Universal DVD Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March 16, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23259%20-%20Oppo%20DV-983H%201080p%20Up-Converting%20Universal%20DVD%20Player&amp;bodytext=Well%20the%20folks%20at%20Oppo%20Digital%20are%20at%20it%20again.%20This%20time%20they%20have%20released%20a%20new%20high%20end%20Up-Converting%20DVD%20player.%20The%20DV-983H%20is%20the%20new%20gold%20standard%20in%20up-converting%20DVD%20players.%20It%20incorporates%20Video%20Reference%20Series%20processing%20%28VRS%20by%29%20Anchor%20Bay.%20This%20is%20the%20same%20technology%20used%20in%20video%20processors%20costing%20%243000%20or%20more.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<br>
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-18.mp3">Listen Now - mp3</a>
<br>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<br>
<a href="http://www.htguys.com">Website</a>
<br>
<br>
<div><strong>Today's Show:</strong></div>
<br>
<div><a href="http://www.oppodigital.com/?partner=825" target="_blank">Oppo DV-983H 1080p Up-Converting Universal DVD Player (MSRP $399)</a></div>
<div>&nbsp;</div>
<div>Well
the folks at Oppo Digital are at it again. This time they have released
a new high end Up-Converting DVD player. The DV-983H is the new gold
standard in up-converting DVD players. It incorporates Video Reference
Series processing (VRS by) <a href="http://www.anchorbaytech.com/vrs_technology/" target="_blank">Anchor Bay</a>. This is the same technology used in video processors costing $3000 or more.</div>
<div>&nbsp;</div>
<div><strong>Features</strong>:</div>

<div>
<ul>
<li>High definition up-conversion up to 1080p</li>
<li>7.1-channel audio with Dolby Digital Surround EX decoding</li>
<li>24-bit, 192kHz, 8-channel high-resolution audio D/A converters</li>
<li>PAL and NTSC compatible with multiple output formats - NTSC: 1080p/1080i/720p/480p, PAL: 1080p/1080i/720p/576p</li>
<li>USB 2.0 interface for video, picture and music playback</li>
<li>High resolution photo slide show</li>
<li>RS232 and external IR in/out remote control integration</li>
<li>Multi-disc resume, various play mode (repeat, A-B repeat, shuffle, random and bookmark)</li>

<li>Plays XviD and .SRT, .SMI, .IDX and .SUB format</li></ul></div>
<div><strong>Performance</strong>:</div>
<div>As
in previous reviews we tested the 983 out of the box without any
changes to the settings on the player. We tested the player on a
properly calibrated 65 inch 1080p DLP TV so we did not adjust the
player's settings. &nbsp;We watched Spiderman II (Superbit), Blackhawk Down,
and Monster's Inc. Monster's Inc looked like it was an HD version of
the movie, the other two movies showed a nice improvement over the the
DVD version watched on our Mac Mini (the Mac Mini also upconverts). &nbsp;</div>
<div>&nbsp;</div>
<div>Like previous models we were impressed with
how good our old DVDs looked. You could easily tell there was an
improvement in the picture! The Superbit version of Spiderman Two
looked very good and our torture test of night scenes in Blackhawk down
were no problem for this player. But these are subjective tests. We put
the HQV Benchmark DVD into the player and started going through
subjective tests. This disc is designed to put difficult material on
screen to test how well the player can deal with them. The player
passed each test with flying colors. And this where the case is made
for buying this player over one of their other lower cost units.</div>
<div>&nbsp;</div>
<div>The DV-983H uses are more sophisticated algorithms
and electronics to handle more difficult video which results in better
performance. To be honest some of the improvements are hard to see
unless someone points it out to you. When comparing some video you
won't see a difference between the Oppo players. But in video that is
not transfered to DVD very well the 983H will be able to improve the
picture while the other Oppo players will actually make them worse.&nbsp;</div>
<div>&nbsp;</div>
<div>Back when we reviewed the Oppo DV-981HD (<a href="/archive/2006/December222006.html" target="_blank">Podcast #130</a>)
we viewed a movie called Bend it Like Beckham. In that review were not
impressed with the 1080p playback of the movie noting noise around
faces and in soccer scenes. The 983H was able to render these scenes
with much improved results. &nbsp;</div>

<div>&nbsp;</div>
<div>For Audio, we set the player to process Dolby
Digital on the 983 and send it to the receiver as PCM as well as
sending bit stream to the receiver for decoding there. We found no
discernible difference in our setup. The 983 supports DVD-Audio and
Super Audio CD which will make use of the 24 bit, 192kHz D/A
converters. That is if you are using the 8 analog outputs of the
player. As with the other Oppo players we were quite satisfied with the
audio performance of music CDs.</div>
<div>&nbsp;</div>
<div><strong>Odds and Ends:</strong></div>
<div>The
menus are basic and easy to navigate. The remote control is basic but
has some dedicated keys that will make switching resolution easy. If
you opt for a universal remote these commands will need to be mapped to
a soft key. &nbsp;The player can be made region free so you&nbsp;can watch DVDs
from anywhere as well. To make the Oppo Region Free do the following:
(Press Setup. Press "9210" in quick succession. A new window will
appear. Press "0". Press Setup to exit.). It comes with an HDMI cable
too! More information can be found online at the&nbsp;<a href="http://www.oppodigital.com/?partner=825" target="_blank">Oppo Digital Website</a>.</div>
<div>&nbsp;</div>
<div><strong>Conclusion</strong>:</div>
<div>We
really liked the Oppo Digital DV-983H. So who should spend $399 on it?
If you are really into movies and have a large collection of DVDs this
is the player that will do the best with everything you have. If you
have a front projector and don't want to spend $3000 on a video
processor this player will do the same thing, at least for your DVDs,
for a fraction of the cost. If you are a videophile and you want the
absolute best video performance the 983 is for you. Finally, if you
just can't bring yourself to buying a Blu Ray Player this is the unit
for you.</div>
<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March 16, 2008 08:02 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1299
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
					AND entry_id <> 1299
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_259_-_oppo_dv-983h_1080p_up-converting_universal_dvd_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
