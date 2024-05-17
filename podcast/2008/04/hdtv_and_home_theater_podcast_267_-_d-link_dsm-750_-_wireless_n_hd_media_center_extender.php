<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1341";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1341 AND placement_is_primary = 1";
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
	<meta name="keywords" content="media center, center extender, link dsm, vista media, features media, media, Media, center, extender, Center, dsm, Link, DSM, link, setup, network, connected, wired, found, Extender, wireless, vista, Vista, using, features" />
	<meta name="description" content="Back in Episode #178 (June, 2007) we did a feature on the Vista Media Center. For that review we used an HP m8000n to run the Media Center.  We talked about the features of the Media Center and how one could use the computer to run spreadsheets during the day and watch live TV or movies using an extender device at night. If you are not familiar with the features of the Media Center that would be a good show to go back and listen to. We will only be covering specific features of the Media Center as they pertain to the DSM-750. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #267 - D-Link DSM-750 - Wireless N HD Media Center Extender</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #267 - D-Link DSM-750 - Wireless N HD Media Center Extender'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #267 - D-Link DSM-750 - Wireless N HD Media Center Extender</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 14, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23267%20-%20D-Link%20DSM-750%20-%20Wireless%20N%20HD%20Media%20Center%20Extender&amp;bodytext=Back%20in%20Episode%20%23178%20%28June%2C%202007%29%20we%20did%20a%20feature%20on%20the%20Vista%20Media%20Center.%20For%20that%20review%20we%20used%20an%20HP%20m8000n%20to%20run%20the%20Media%20Center.%20%20We%20talked%20about%20the%20features%20of%20the%20Media%20Center%20and%20how%20one%20could%20use%20the%20computer%20to%20run%20spreadsheets%20during%20the%20day%20and%20watch%20live%20TV%20or%20movies%20using%20an%20extender%20device%20at%20night.%20If%20you%20are%20not%20familiar%20with%20the%20features%20of%20the%20Media%20Center%20that%20would%20be%20a%20good%20show%20to%20go%20back%20and%20listen%20to.%20We%20will%20only%20be%20covering%20specific%20features%20of%20the%20Media%20Center%20as%20they%20pertain%20to%20the%20DSM-750.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-15.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
Back in <a href="http://www.htguys.com/archive/2007/June08.html">Episode #178</a> (June, 2007) we did a feature on the Vista Media Center. For that review we used an <a href="http://www.htguys.com/shop.php?id=B000O58Q2U">HP m8000n</a> to run the Media Center.  We talked about the features of the Media Center and how one could use the computer to run spreadsheets during the day and watch live TV or movies using an extender device at night. If you are not familiar with the features of the Media Center that would be a good show to go back and listen to. We will only be covering specific features of the Media Center as they pertain to the DSM-750. </p>

<p><strong>D-Link DSM-750 - Wireless N HD Media Center Extender</strong> - $300 online (<a href="http://www.htguys.com/shop.php?id=B001193F1K">Buy Now</a>)<br />
When we recorded Episode #178 the only media center extender that was available was the Xbox 360. More recently on <a href="http://www.htguys.com/archive/2008/March25.html">Episode #261</a> (March, 2008) we highlighted five new Media Extender devices that are either on the market of will be on the market soon. Today we had the opportunity to put the D-Link DSM-750 through its paces. For this review we ran Microsoft Vista Media Center on an HP laptop and connected to the HD Homerun Ara already had setup in his home. For this review we are not going to go into the features <br />
 <br />
<strong>Features</strong><ul><li>The DSM-750 includes a MediaLounge Media Player mode, which allows a Windows XP OS-based PC to stream music, photos, and videos. This mode also enables streaming of music, photos, and videos stored on a Networked Attached Storage2 or USB Flash Drive3</li><li>The DSM-750 supports HD video resolutions of up to 1080i so you can enjoy life-like picture quality on your 1080p-capable HDTV</li><li>The DSM-750 supports HD video in either Windows Media Video 9 or MPEG-2/47 format</li><li>Dual User Modes: Extender for Windows Media Center and D-Link MediaLounge</li></ul><br />
<strong>Setup</strong><br />
The 750 was uses both wired and wireless. It supports 802.11N which theoretically has plenty of bandwidth for HD. More on that later. For the wired test we connected an HDMI cable to the player and an HDMI switching receiver. We connected Ethernet and power and that was it! We were ready to turn on the system. Once turned on we entered the 750's setup menu so we could configure it to join the network. Once that was done. The Vista PC immediately recognized the player and asked if you wanted to add the extender. This was done by entering two four digit numbers that the extender was displaying into the Media Center PC.<br />
 <br />
For the second part of the setup we disconnected the Ethernet cable of connected three antennas to the 750. We had to go into the setup screens again to enter the wireless password which would allow it to join the network. We did this and were connected in a few seconds. But we found odd was that the Media Center wanted us to re-configure the extender. We had to input a different 8 digit sequence and let the extender and the media center rediscover each other. The process was slower using wireless. Now this is not a big deal as most people won't be switching between WiFi and wired but we found it odd the the changing the network connection would require setting up the extender all over again.<br />
 <br />
The whole process was straight forward and did not require manuals. D-Link includes an easy to understand step by step guide to getting you up an running in no time.<br />
 <br />
<strong>Impressions</strong><br />
When using the 750 with a wired connection to the network the system was very responsive. It output a 1080i picture that looked as good as the 1080i signal Ara watches with his EyeTV/HD Homerun setup. The box was very quiet, far quieter than the Xbox 360. The WiFi setup was not as snappy as the wired and we have an N network. In fact it got a little annoying as there would be pauses between remote control actions. We later found out that they are recommending using an N network that is on the 5GHz band. This will eliminate interference from cordless phones or microwave ovens. Which may have been the issue. D-Link includes a remote but we found that the Harmony 880 that was already setup to work with the Xbox 360 worked with the D-Link Extender.  <br />
 <br />
<strong>D-Link Media Lounge</strong><br />
This is a cool feature that 750 has. It works as a media player with the computers you have on your network. Even though the Macintosh is not explicitly supported, it found the computers and was able to playback music and photos that were stored there. It will do the same with NAS devices that support DLNA or UPnP. The DSM-750 can also play music, videos, and photos that have been copied to a USB stick. <br />
 <br />
<strong>What we liked:</strong><ul><li>Easy Setup</li><li>Very Quiet</li><li>The ability to play A/V files on networked computers via MediaLounge</li></ul><br />
 <br />
<strong>What we disliked:</strong><ul><li>Slow interface with wireless</li><li>No 1080p upconversion</li></ul></p>

<p><strong>Conclusion</strong><br />
So if you are looking for a Microsoft Vista Media Center extender and don't want to put a game system into your home theater. The D-Link DSM-750 is a lower cost alternative that looks like it belongs in your living room or equipment rack. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 14, 2008 11:27 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1341
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
					AND entry_id <> 1341
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_267_-_d-link_dsm-750_-_wireless_n_hd_media_center_extender.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
