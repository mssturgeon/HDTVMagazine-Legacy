<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1552";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1552 AND placement_is_primary = 1";
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
	<meta name="keywords" content="media player, western digital, hard drive, dolby digital, does support, player, media, digital, Digital, Media, hard, drive, Western, content, western, mpeg, video, setup, dolby, drives, Dolby, does, supports, support, MPEG" />
	<meta name="description" content="Today we take a look at the Western Digital WD TV HD Media player. The WD TV HD Media Player is a device about the size of an external hard drive that plays A/V content from USB storage media. The WD TV HD Media player is optimized for the WD &quot;My Passport&quot; line of hard drives but it will work with other USB devices. The player supports 1080p for content and menus navigation." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #328 - Western Digital WD TV HD Media Player</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #328 - Western Digital WD TV HD Media Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #328 - Western Digital WD TV HD Media Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>November 14, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23328%20-%20Western%20Digital%20WD%20TV%20HD%20Media%20Player&amp;bodytext=Today%20we%20take%20a%20look%20at%20the%20Western%20Digital%20WD%20TV%20HD%20Media%20player.%20The%20WD%20TV%20HD%20Media%20Player%20is%20a%20device%20about%20the%20size%20of%20an%20external%20hard%20drive%20that%20plays%20A%2FV%20content%20from%20USB%20storage%20media.%20The%20WD%20TV%20HD%20Media%20player%20is%20optimized%20for%20the%20WD%20%22My%20Passport%22%20line%20of%20hard%20drives%20but%20it%20will%20work%20with%20other%20USB%20devices.%20The%20player%20supports%201080p%20for%20content%20and%20menus%20navigation.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-11-14.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Today we take a look at the Western Digital WD TV HD Media player. The WD TV HD Media Player is a device about the size of an external 
hard drive that plays A/V content from USB storage media. The WD TV HD Media player is optimized for the WD "<a target="_blank" href="http://www.htguys.com/shop.php?id=B0012GR02W">My Passport</a>" line of hard 
drives but it will work with other USB devices. The player supports 1080p for content and menus navigation.<br><br>
<a target="_blank" href="http://www.westerndigital.com/en/products/products.asp?driveid=572&language=en">Western Digital WD TV HD Media Player (WDAVN00)</a><br>
<strong>(<a target="_blank" href="http://www.htguys.com/shop.php?id=B001JZFQU4">Buy Now $125</a>)</strong><br><br>
<strong>Features:</strong><br>
<ul><li>Thumbnail and list views – Browse your content by filename or by thumbnails of photos, album covers and movie cover art.</li>
<li>Media Library – This unique feature lets you view all your media by media type in one menu regardless of its location in folders or 
drives. You can view your content by categories such as genre, album, artist and date.</li>
<li>Search – Search by genre, title, artist, filename and partial filename.</li>
<li>Access two USB drives simultaneously</li>
<li>HDMI and composite video connections</li>
<li>Includes free media conversion software - ArcSoft MediaConverter™ 2.5</li>
<li>Ultra-compact design</li>
</ul><br>
<strong>File Formats Supported</strong>
<ul>
<li>Music - MP3, WMA, OGG, WAV/PCM/LPCM, AAC, FLAC, Dolby Digital, AIF/AIFF, MKA</li>
<li>Photo - JPEG, GIF, TIF/TIFF, BMP, PNG</li>
<li>Video -MPEG1/2/4, WMV9, AVI (MPEG4, Xvid, AVC), H.264, MKV, MOV (MPEG4, H.264)</li>
<li><em>Note:
<ul><li>MPEG2/4, H.264, and WMV9 supports up to 1920x1080p 24fps, 1920x1080i 30fps, 1280x720p 60fps resolution</li>
<li>An audio receiver is required for surround sound output. AAC/Dolby Digital decodes in 2 channel output only</li>
<li>JPEG does not support CMYK or loss less.</li>
<li>BMP supports uncompressed format only.</li>
<li>TIF/TIFF supports single layer only.</li>
</ul></ul></em><br>
<strong>Setup:</strong><br>
Setup was trivial. There is no network support so setup consisted of connecting power, HDMI, and plugging in an 
external hard drive. There are also composite connections available for SDTVs. The player is actually setup for HDTV 
as default. So many devices are setup to be 4:3 right out of the box.<br><br>
<strong>Performance:</strong><br>
The WD TV HD Media Player worked quite well. The menus were more polished than we expected. They looked good displayed 
on a 65 inch 1080p TV. Navigation was simple and intuitive. Once a drive is plugged in the media player scans it for 
content and organizes it by type. You don't have to go hunting for photos, music or video. You can even search for a 
file by name. We attached a universal card reader, an iPod, and an external Western Digital Hard Drive. All three were 
recognized. The iPod had limited functionality, it was attached just for fun. The device is optimized for Western Digital's 
"My Passport" line of drives and supports FAT32, NTFS, HFS+ (no journaling) file formats. But we found no issues with any 
drive we attached to it.<br><br>
The WD TV HD Media player does not support protected premium content such as movies or music from the iTunes® Store, 
Cinema Now, Movielink®, Amazon Unbox™, and Vongo®. Nor will it decode Dolby Digital beyond two channel. But what it 
does do is playback pretty much any video you can throw at it. Ara have been busy converting his VHS Library to mpeg4 
and found that the media player not only played the video but did a good job of upconverting it to 1080p.<br><br>
If you have listened to the podcast for a while you know that Ara has an HD Homerun and can record OTA HDTV programs on 
his computer. These programs are both 1080i and 720p and contain Dolby Digital 5.1 audio. For this evaluation a few 
recordings were copied to the WD Hard Drive which was then connected to the media player. They were immediately recognized 
and made available via the player. The looked beautiful when displayed on the TV and while the sound was not 5.1 it did 
sound clear. The only real complaint we have about the player is that it does not support Dolby Digital 5.1 audio. Music 
playback is just as easy. Copy mp3 or AAC files to the hard drive and you are good to go. The Album art is even displayed 
in the GUI.<br><br>
The player comes with a nicely laid out remote control. Its small and does not have a ton of buttons.<br><br>
<strong>Conclusion:</strong><br>
Ara was fully anticipating selling the player after he was done with the review. However, due to its tiny size and ease 
of transport, he now sees it as a great device for travel. And yes, Western Digital got it right by making it a perfect 
complement to the "<a target="_blank" href="http://www.htguys.com/shop.php?id=B0012GR02W">My Passport</a>" line of hard drives.
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>November 14, 2008 06:52 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1552
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
					AND entry_id <> 1552
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_328_-_western_digital_wd_tv_hd_media_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
