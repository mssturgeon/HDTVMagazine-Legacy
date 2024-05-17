<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1504";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1504 AND placement_is_primary = 1";
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
	<meta name="keywords" content="slingbox pro, local network, via internet, significant improvement, atsc tuner, slingbox, Slingbox, Pro, pro, internet, Internet, video, new, network, mbps, via, Mbps, improvement, see, Ara, Video, software, Slingplayer, local, performance" />
	<meta name="description" content="We reviewed the original Slingbox way back in 2005. Ara currently has two of them and has been able to watch TV from his hotel in Chicago, Denver, and Europe. But the main way the Slingbox is used in the Derderian family is to extend TV reception to any room in the house. While the Slingbox works well via the Internet it works really well on your local network. The issue with Internet usage is bandwidth. First, if your upstream bandwidth is limited it really doesn't matter how much downstream bandwidth you have. In Ara's home the upstream is capped at 1 Mbps (although lately he has exceeded that)." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #316 - Vudu HDX and Slingbox Pro HD</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #316 - Vudu HDX and Slingbox Pro HD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #316 - Vudu HDX and Slingbox Pro HD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October  3, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23316%20-%20Vudu%20HDX%20and%20Slingbox%20Pro%20HD&amp;bodytext=We%20reviewed%20the%20original%20Slingbox%20way%20back%20in%202005.%20Ara%20currently%20has%20two%20of%20them%20and%20has%20been%20able%20to%20watch%20TV%20from%20his%20hotel%20in%20Chicago%2C%20Denver%2C%20and%20Europe.%20But%20the%20main%20way%20the%20Slingbox%20is%20used%20in%20the%20Derderian%20family%20is%20to%20extend%20TV%20reception%20to%20any%20room%20in%20the%20house.%20While%20the%20Slingbox%20works%20well%20via%20the%20Internet%20it%20works%20really%20well%20on%20your%20local%20network.%20The%20issue%20with%20Internet%20usage%20is%20bandwidth.%20First%2C%20if%20your%20upstream%20bandwidth%20is%20limited%20it%20really%20doesn%27t%20matter%20how%20much%20downstream%20bandwidth%20you%20have.%20In%20Ara%27s%20home%20the%20upstream%20is%20capped%20at%201%20Mbps%20%28although%20lately%20he%20has%20exceeded%20that%29.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-03.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
We reviewed the original Slingbox way back in 2005 (<a href="http://www.htguys.com/archive/2005/slingbox.html" target="_blank">Review Here</a>). Ara currently has two of them and has been able to watch TV 
from his hotel in Chicago, Denver, and Europe. But the main way the Slingbox is used in the Derderian family is to extend TV 
reception to any room in the house. While the Slingbox works well via the Internet it works really well on your local network. 
The issue with Internet usage is bandwidth. First, if your upstream bandwidth is limited it really doesn't matter how much 
downstream bandwidth you have. In Ara's home the upstream is capped at 1 Mbps (although lately he has exceeded that).<br><br>
<strong>
<a href="http://www.slingmedia.com/go/slingbox-prohd" target="_blank">Slingbox Pro HD</a> (<a href="http://www.htguys.com/shop.php?id=B001EZRJZE" target="_blank">$300 Buy Now</a>)</strong><br><br>
When we saw the Slingbox Pro HD come to market we were very excited. We realize that the dream of streaming your HD channels 
via the Internet is a difficult proposition. Regardless, a significant improvement over the current state is welcome. And 
that's what the new Slingbox Pro HD is. A significant improvement for some of us. On your local network, it is a significant 
improvement for all!<br><br>
The new Slingbox is setup for HD. It accepts component HD Video, SD Video over RCA and S-Video, and it has an ATSC tuner built 
in. If you include the ATSC tuner the Pro HD accepts HD from three sources. Those can range from Satellite or Cable Boxes to 
Windows Media Centers and AppleTVs. The ATSC tuner is a great addition. You can watch something over the air via the Slingbox 
without affecting any of the other devices connected to it.<br><br>
<strong>Setup</strong><br>
We connected the Pro HD to Ara's Satellite Receiver, Antenna Cable, IR Emitter, Ethernet Cable and power. Then we installed the 
Slingplayer on his wife's laptop. Its important to note that the new software only works with PCs for now. Once installed, the 
Mac Slingplayer will recognize the ProHD but video will only be played in SD. Albeit high quality SD. You go through a few 
questions and before you know it you are ready to go. The Slingplayer has just about every device IR code so controlling your 
equipment is not an issue. Then it was on to scanning for digital channels. That process took about 10 minutes. The last 
process was setting up the network to see the Slingbox over the Internet. Total investment in time, about 40 minutes.<br><br>
<strong>Performance</strong><br>
A new feature that is being introduced on the Pro HD is the Integrated TV Guide built into the Sling software. If you have 
used the Slingbox with a set top box you know that pulling up the guide is a little cumbersome due to the delay in sending 
the command via the Internet and waiting for its response to show up on the screen. The integrated guide eliminates this. 
You pull up a guide that is representative of the channels you subscribe to or receive over the air. When you select a show 
the software sends the change channel command to the set top box.<br><br>
Another new feature is the Live Video Buffer on your computer. This makes your PC act like a DVR. You can't record shows but 
you can pause, fast forward, and rewind live TV for up to 60 minutes. The previous two new features are Windows only for now. 
No date has been given for the Mac version.<br><br>
<em>Internet</em> - The best way to describe the Internet performance is that it is an improvement over the previous versions. While we 
wouldn't call it HD, we did think it looked very good. This is a function of your Internet connection. At Ara's home the 
upstream bit rate is just a tad over 1 Mbps. Not enough for the 1.5 Mbps required to get what Slingmedia calls HD. Its 
important to know that. If you don't have the 1.5 Mbps going up you won't see the Pro HD perform at its optimum. The 
Slingplayer software has HD optimization built into it to try and adjust the performance to match the connection speed. Our 
advice is to leave this on. It will make the appropriate adjustments on the fly.<br><br>
<em>Local Network</em> - Here we saw a major improvement. The picture was better than DVD quality and in a side by side 
comparison we'd say the Pro HD did an great job. When compared to the SD version, you could see a lot of artifacts that 
weren't visible on the Pro HD.  At points we were seeing a data rate that exceeded 8Mbps. So WiFi would work but you need 
a clean signal. The other thing we noticed was that the computer would have some issues with smoothness of the video. To 
really see the thing fly you need a 3GHz Processor or a 2GHz dual core processor. A fast video card wouldn't hurt either.
<br><br>
<strong>Conclusion</strong><br>
This is a good product that may not be for everyone. If you want to watch your HDTV programming via the Internet and you 
have a good broadband connection on both ends, this is your only real solution. On your local network, the Slingbox Pro 
HD will turn your computers into HDTVs. If you have a TV connected to one of your PCs, you can watch recordings on a DVR 
in one room on a TV in another. While we are very pleased with the performance of the Pro HD we feel that the introduction 
of the SlingCatcher will further enhance the products value. With the SlingCatcher you will be able to control the Slingbox 
without a computer effectively providing you a multi room DVR.<br>
<br>
<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October  3, 2008 12:13 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1504
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
					AND entry_id <> 1504
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_316_-_vudu_hdx_and_slingbox_pro_hd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
