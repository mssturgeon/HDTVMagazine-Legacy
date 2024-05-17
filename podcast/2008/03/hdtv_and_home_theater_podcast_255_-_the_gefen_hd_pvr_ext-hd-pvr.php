<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1288";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1288 AND placement_is_primary = 1";
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
	<meta name="keywords" content="recording internal, internal hard, hard drive, internal drive, copy protected, pvr, PVR, recording, drive, record, hours, Hours, internal, Gefen, gefen, set, want, card, take, bit, copy, hdmi, hard, mac, digital" />
	<meta name="description" content="If you are like us you probably have one or two recordings on your DVR that you simply won't delete. But what if you want to archive the recording or watch it on your computer? Your choices in the past have been to down convert the program to 480i and record it on a DVD recorder or a computer with a capture card.  Today we talk about a product that we saw at CES that solves this problem. The Gefen HD PVR (EXT-HD-PVR) " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #255 - The Gefen HD PVR (EXT-HD-PVR)</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #255 - The Gefen HD PVR (EXT-HD-PVR)'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #255 - The Gefen HD PVR (EXT-HD-PVR)</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March  3, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23255%20-%20The%20Gefen%20HD%20PVR%20%28EXT-HD-PVR%29&amp;bodytext=If%20you%20are%20like%20us%20you%20probably%20have%20one%20or%20two%20recordings%20on%20your%20DVR%20that%20you%20simply%20won%27t%20delete.%20But%20what%20if%20you%20want%20to%20archive%20the%20recording%20or%20watch%20it%20on%20your%20computer%3F%20Your%20choices%20in%20the%20past%20have%20been%20to%20down%20convert%20the%20program%20to%20480i%20and%20record%20it%20on%20a%20DVD%20recorder%20or%20a%20computer%20with%20a%20capture%20card.%20%20Today%20we%20talk%20about%20a%20product%20that%20we%20saw%20at%20CES%20that%20solves%20this%20problem.%20The%20Gefen%20HD%20PVR%20%28EXT-HD-PVR%29%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-03.mp3">Listen Now - mp3</a>
<br>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<br>
<a href="http://www.htguys.com">Website</a>
<br>
<br>
<strong>Today's Show:</strong><br>

<div>If you are like us you probably have one or two recordings on your DVR that you simply won't delete. But what if you want to archive the recording or watch it on your computer? Your choices in the past have been to down convert the program to 480i and record it on a DVD recorder or a computer with a capture card.<strong>&nbsp; </strong>Today we talk about a product that we saw at CES that solves this problem. The<strong> </strong><a id="wz04" href="http://www.gefen.com/kvm/product.jsp?prod_id=4306" target="_blank" title="Gefen HD PVR (EXT-HD-PVR) MSRP $999">Gefen HD PVR (EXT-HD-PVR)</a><font color="black" face="Arial" size="2"> </font></div>
<div><strong>&nbsp;</strong></div>
<div>
<div> 
<div>
<div> </div></div>
<div> </div>

<div> </div>
<div> </div></div>
<div><font color="black" face="Arial" size="2"></font>The people at Gefen have come up with a new PVR that will record from your DVR or Set Top Box via the HDMI connection in HD! The only caveat is that the content encoded with HDCP can not be moved off of the internal hard drive. The Gefen EXT-HD-PVR is available at the HT Guys Store and sells for $825.<font color="black" face="Arial" size="2"><br>
<br>
<strong>Features:</strong><br>
<br>
</font>
<ul>
<li>
<p><font face="Arial, Helvetica, sans-serif" size="-1">Video recording and playback at resolutions of 480i  to 1080i</font></p></li>
<li>
<p><font face="Arial, Helvetica, sans-serif" size="-1">Single-Button Instant Recording to SD Flash Media or Internal Hard Disk Drive </font></p></li>

<li>
<p><font face="Arial, Helvetica, sans-serif" size="-1">Pause, Mute, Fast Forward &amp; Fast Backwards playback controls</font></p></li>
<li>
<p><font face="Arial, Helvetica, sans-serif" size="-1"><font color="#000000"><font face="Arial, Helvetica, sans-serif" size="-1">Programmable unattended recording</font></font></font></p></li></ul><br>
      Set up took us two minutes. Connect the HDMI cable from your source (the PVR accepts two HDMI, one Component, and one composite input) and one to your TV and your set. We were recording to the internal hard drive in a matter of seconds. You have the option of recording to the internal drive or a Secure Digital Flash drive. You can also copy a recording from the internal drive to the SD flash drive as long as the recording is not HD or copy protected. If you want to get an unprotected HD recording off the DVR it must be recorded directly to the SD drive.<br>
<br>
      You can set the player to record programs in a way that is reminiscent of VCRs. You set a date and a time for the PVR to turn on for how long to record. There is a IR Blaster that can be used to turn on and set a set top box to the correct channel for recording. You can also take a SD card from your digital camera and insert it into the SD card slot and look at pictures via the HD-PVR.<br>
<br>
      The following table outlines what you can record on the internal 80GB hard drive:<br>

<br>
<div>
<table style="text-align: left; margin-left: auto; margin-right: auto;" id="wfjw" border="2" cellpadding="3" cellspacing="0" height="188" width="517"><tbody><tr><td style="text-align: center;" width="20%"><strong>Quality</strong></td><td style="text-align: center;" width="20%"><strong>HD Bit Rate</strong></td><td style="text-align: center;" width="20%"><strong> HD Record Time</strong></td><td style="text-align: center;" width="20%"><strong>SD Bit Rate</strong></td><td style="text-align: center;" width="20%"><strong>SD Record Time </strong></td></tr><tr><td width="20%">Extra Fine (EX)</td><td width="20%">10 Mb<br>
</td><td width="20%">17 Hours<br>
</td><td width="20%">4 Mb<br>
</td><td width="20%">44 Hours<br>

</td></tr><tr><td width="20%">Normal (NOR)</td><td width="20%">7.5 Mb<br>
</td><td align="left" width="20%">23 Hours<br>
</td><td width="20%">3 Mb<br>
</td><td width="20%">59 Hours<br>
</td></tr><tr><td width="20%">Economic (ECO)</td><td width="20%">5 Mb<br>
</td><td width="20%">35 Hours<br>
</td><td width="20%">2 Mb<br>
</td><td width="20%">88 Hours<br>

</td></tr><tr><td width="20%">SuperEco (S.E.)</td><td width="20%">3.5 Mb<br>
</td><td width="20%">50 Hours<br>
</td><td width="20%">1 Mb<br>
</td><td width="20%">133 Hours<br>
</td></tr></tbody></table>
<div style="text-align: center;">&nbsp;</div></div>Recordings made on EX mode looked very good when watched back on the 65 in Mitsubishi DLP. Normal quality was about equivalent to the HD AppleTV downloads and Economic was similar to DVD. SuperEco was almost DVD quality<br>
      &nbsp;<br>
      The Gefen HD-PVR includes decoder software which allows you to play your recorded movies on your Windows computer. You will still need Windows Media Player to watch the movies. The files are recorded as .mp4 files which should play just fine on the Macintosh but we couldn't get our Mac to read the SD card. The Mac reported that it was an unreadable format.<br>

<br>
      Some things to consider. The PVR does not support Dolby Digital 5.1 audio so your recordings will only be in Stereo. The GUI is a bit rough but functional. The remote is a bit complicated and button layout is not intuitive.<br>
<br>
<strong>Conclusion:</strong><br>
      The Gefen PVR does what Gefen says it will with good results. We would have liked to see Dolby Digital audio and the ability to take a SD card and plug it directly into the Mac. The price is a little steep for what you get but if you want to record HD via HDMI, this is the way to go. If you want to move the content to your PC make sure what you are recording is not copy protected. Another plus is that the unit is small enough to take take with you to a friends house. It is roughly the size of a Mac Mini, so if you want to watch a copy protected recording on the internal drive you can easily take the PVR with you.
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March  3, 2008 09:10 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1288
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
					AND entry_id <> 1288
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_255_-_the_gefen_hd_pvr_ext-hd-pvr.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
