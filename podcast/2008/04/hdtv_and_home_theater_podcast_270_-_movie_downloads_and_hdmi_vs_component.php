<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1366";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1366 AND placement_is_primary = 1";
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
	<meta name="keywords" content="point view, hdmi cable, professional installer, component cables, foot hdmi, hdmi, HDMI, component, cables, cable, Component, professional, audio, view, version, make, video, feet, work, show, long, point, foot, podcast, time" />
	<meta name="description" content="We receive emails all the time from listeners asking us about cables. Many ask what they would be missing of they were to use Component cable instead of HDMI. From a picture point of view your not giving up too much. From an audio point of view you would need to run eight RCA cables to bring in Dolby True HD or DTS MA sound tracks. Eight cables for audio and three for video. That's eleven cables to do the work of one HDMI cable. So with such a big advantage in cable management alone, we found it interesting that professional installers preferred to use component cables." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #270 - Movie Downloads and HDMI vs. Component</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #270 - Movie Downloads and HDMI vs. Component'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #270 - Movie Downloads and HDMI vs. Component</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 25, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23270%20-%20Movie%20Downloads%20and%20HDMI%20vs.%20Component&amp;bodytext=We%20receive%20emails%20all%20the%20time%20from%20listeners%20asking%20us%20about%20cables.%20Many%20ask%20what%20they%20would%20be%20missing%20of%20they%20were%20to%20use%20Component%20cable%20instead%20of%20HDMI.%20From%20a%20picture%20point%20of%20view%20your%20not%20giving%20up%20too%20much.%20From%20an%20audio%20point%20of%20view%20you%20would%20need%20to%20run%20eight%20RCA%20cables%20to%20bring%20in%20Dolby%20True%20HD%20or%20DTS%20MA%20sound%20tracks.%20Eight%20cables%20for%20audio%20and%20three%20for%20video.%20That%27s%20eleven%20cables%20to%20do%20the%20work%20of%20one%20HDMI%20cable.%20So%20with%20such%20a%20big%20advantage%20in%20cable%20management%20alone%2C%20we%20found%20it%20interesting%20that%20professional%20installers%20preferred%20to%20use%20component%20cables.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-25.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
We receive emails all the time from listeners asking us about cables. Many ask what they would be missing of they were to use Component cable instead of HDMI. From a picture point of view your not giving up too much. From an audio point of view you would need to run eight RCA cables to bring in Dolby True HD or DTS MA sound tracks. Eight cables for audio and three for video. That's eleven cables to do the work of one HDMI cable. So with such a big advantage in cable management alone, we found it interesting that professional installers preferred to use component cables.<br />
<strong><br />
HDMI or Component from a professional Installer's Point of View</strong></p>

<p>On today's show we discuss an article in <a href="http://www.cepro.com/" target="_blank">CE Pro</a> titled <a href="http://www.ce-pro.com/article/hdmi_or_component_integrators_weigh_in/" target="_blank">HDMI or Component? Integrators Weigh In</a> written by Donna Englander that will explain why so many professionals prefer component cables. It all comes down to reliability.</p>

<p>The main job of a professional installer is to make the viewing experience as bullet proof as possible. HDMI in its early stages was anything but that. Many professional installers agree that the specification has come a long way since its version 1.0 implementation but few are ready to give HDMI another shot. With version 1.3 most of the handshaking issues have been eliminated and devices that support V1.3 play together nicely. But there is no denying that for long runs (50 feet or more) HDMI has its issues. If you are using HDMI for a local setup we don't think anyone will argue that its the easiest and best way to go. If you are setting up a projector room or whole house video where the equipment and the projector are separated by a large distance component is the only real way to go.</p>

<p><strong>The main complaints about HDMI cables are:</strong><ul><li>You can't reliably run HDMI cables as far as component cables</li><li>You can't make custom lengths</li><li>No good HDMI via Cat 5</li><li>Cost</li><li>HDCP not working</li></ul><br />
<strong>Advantages of HDMI:</strong><ul><li>Audio and video on the same cable</li><li>Future Proof</li></ul><br />
<strong>Things to Consider:</strong><ul><li>The HT Guys have successfully used a 50 foot HDMI cable for a review (<a target="_blank" href="http://www.htguys.com/archive/2006/September082006.html">Podcast 100</a>)</li><li>HDMI version 1.3 is the is the most reliable when it comes to HDCP handshaking</li><li>You can buy a 25 foot HDMI 1.3a cable for around $40, 50 feet goes for about $135 and 100 feet go for about $255. Still less than a premium priced two meter HDMI cable.</li><li>Test the connection prior to completing your installation.</li><li>Make sure you can return the cable if it does not work.<br />
<strong>Additional Reading:</strong> <br />
Long time listener and friend of the show Joe sent us a link to an article in EDN that talks about all the confusion in the HDMI world. It seems that the HDMI group has their work cut out for them. Check out <a target="_blank" href="http://www.edn.com/index.asp?layout=blog&blog_id=400000040&blog_post_id=1270025127">NAB 2008: Audio Precision Ameliorates HDMI Confusion</a>.</p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 25, 2008 09:13 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1366
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
					AND entry_id <> 1366
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_270_-_movie_downloads_and_hdmi_vs_component.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
