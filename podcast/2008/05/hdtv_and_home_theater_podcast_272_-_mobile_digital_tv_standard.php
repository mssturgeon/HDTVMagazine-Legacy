<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1375";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1375 AND placement_is_primary = 1";
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
	<meta name="keywords" content="mobile digital, digital video, digital television, television spectrum, new technologies, mobile, video, digital, television, camera, devices, quality, spectrum, home, time, new, today, standard, broadcast, signals, coalition, HDTV, content, technologies, well" />
	<meta name="description" content="Our recent discussion about the Digital Television transition prompted a few emails asking about emergency preparedness and how the loss of analog TV transmission would affect consumers in the event of a natural (or man-made) disaster. This prompted a great email from one of our listeners about the emerging Mobile Digital TV Standard. And we have a quick review of the Panasonic Lumix DMC-FX35A 10.1MP Digital Camera.
" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #272 - Mobile digital TV standard</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #272 - Mobile digital TV standard'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #272 - Mobile digital TV standard</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May  2, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23272%20-%20Mobile%20digital%20TV%20standard&amp;bodytext=Our%20recent%20discussion%20about%20the%20Digital%20Television%20transition%20prompted%20a%20few%20emails%20asking%20about%20emergency%20preparedness%20and%20how%20the%20loss%20of%20analog%20TV%20transmission%20would%20affect%20consumers%20in%20the%20event%20of%20a%20natural%20%28or%20man-made%29%20disaster.%20This%20prompted%20a%20great%20email%20from%20one%20of%20our%20listeners%20about%20the%20emerging%20Mobile%20Digital%20TV%20Standard.%20And%20we%20have%20a%20quick%20review%20of%20the%20Panasonic%20Lumix%20DMC-FX35A%2010.1MP%20Digital%20Camera.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-02.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
Our recent discussion about the Digital Television transition prompted a few emails asking about emergency preparedness and how the loss of analog TV transmission would affect consumers in the event of a natural (or manmade) disaster. This prompted the following email from one of our listeners (Brady Darvin): </p>

<p><em>I looked all over the Internet for more info on portable TVs with ATSC tuners and found none, but I did find this very interesting and recent article about an entirely new mobile TV standard that could make the need for a portable TV with an ATSC tuner obsolete, and thought you might be interested in looking more into this new standard and perhaps discussing on a future podcast on the subject of “mobile home theater”!  See <a target="_blank" href="http://www.omvc.org/">http://www.omvc.org/</a> for more info.</em></p>

<p><strong>But first, a quick review... <br />
Panasonic Lumix DMC-FX35A 10.1MP Digital Camera</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B0011Z9VVW">Street Price $290 Buy Now</a>)<br />
 <br />
We know this is a show about HDTV and Home Theater so why are we talking about a Digital Camera. Well from time to time we do talk about HD Camcorders and this camera has a very cool feature, it records HD video. Today's discussion will only be about this aspect of the camera. We'll leave the full review to podcasts that focus on digital photography.<br />
 <br />
The FX35 records video in standard definition 4:3 (with various resolutions) and in HD 16:9. It also supports 16:9 DVD quality. The video is recorded to an SD memory card which is not included. For our test we bought an 8GB card for $35. The 8GB card supports eight and a half minutes of 720p 30fps (which is the highest quality) video. DVD quality roughly doubles the time. <br />
 <br />
The video quality is quite good considering it was shot on a camera that costs less than $300. The audio however leaves a lot to be desired. It could just be luck of the draw. We have heard that some camera microphones sound great and others sound pretty bad. Still camera manufacturers do not want to spend any money calibrating the mic so its hit and miss if the mic will work well. <br />
 <br />
We have posted a <a target="_blank" href="http://media.libsyn.com/media/hdtvpodcast/LumixDemo.mp4">link to a recording</a> that was shot with the camera. The 1:42 second video clip is 720p 30 FPS and it has been compressed to 2.6 Mbps. The file size is 32MB. The original video  is also 720p 30 FPS but it has a data rate of 24 Mbps and weighs in at 298 MB. After shooting and playing with the video in iMovie we may just start putting some videos together from time to time.<br />
 <br />
<strong>Mobile Digital TV Standard</strong><br />
 <br />
<strong>The following is taken from their website:</strong><br />
 <br />
The Open Mobile Video Coalition is an alliance of U.S. commercial and public broadcasters committed to the development of mobile digital television. The Coalition’s current members include leading broadcast station groups operating over 420 commercial television stations. Their mission is to accelerate the development of mobile digital broadcast television, and capture the full potential of the digital television spectrum in the United States. The coalition will help identify and encourage broad adoption of technologies that enable mobile reception of digital broadcast television signals, so that consumers can watch television wherever and whenever they want, not just in the home.</p>

<p><strong>What is mobile digital video?</strong><br />
As it pertains to the coalition, mobile digital video is an enhancement to the existing terrestrial digital television system that allows a high quality digital video signal to be received by a moving receiver, either at pedestrian or vehicular speeds.</p>

<p><strong>Why can't I get mobile digital video today?</strong><br />
The terrestrial digital television system was designed to maximize broadcast coverage to fixed locations in homes, and, as such, cannot yet support mobile reception. New technologies are now emerging that allow the current system to add mobile reception without sacrificing in-home coverage, and the Coalition's goal is to accelerate the commercialization and standardization of these new technologies.</p>

<p><strong>Will I be able to get HDTV in a mobile environment?</strong><br />
It is anticipated that mobile digital video will allow for a very high quality picture to be received in a mobile environment, but since mobile receivers have small screens, there will not be a need to broadcast HDTV to a mobile device.</p>

<p><strong>What kind of content will be available?</strong><br />
It is expected that there will be a wide variety of content available for mobile video, including many of the same programs that are available at home today, but the specific content will be decided only when the service is commercialized. In addition, users may be able to access weather, traffic and public safety information.</p>

<p><strong>Will the content be free or will I have to pay?</strong><br />
It is expected that mobile digital video technology will support both free ad-supported and pay models, but the specific business models will be decided when the service is commercialized.</p>

<p><strong>What kinds of devices will it work in?</strong><br />
Most video-capable devices can receive mobile digital video by adding a receiver module to the device. Video-capable devices in the market today include cell phones, video-capable MP3 players, laptop computers, portable game players, digital cameras and camcorders, portable DVD players, personal navigation devices and in-car entertainment systems, among others.</p>

<p><strong>Why is the digital television spectrum better than other spectrum for mobile video?</strong><br />
The digital television spectrum is ideally suited for video because its signals travel long distances, penetrate walls well, can be easily received in a fast moving vehicle, and require only a small antenna. Spectrum that allows signals to travel farther typically require antennas too large for mobile devices, and spectrum that allows for a smaller antenna typically have less range and are more difficult to receive in moving vehicles.</p>

<p><strong>Will broadcasters have to choose between offering HD and mobile television?</strong><br />
The new technologies will allow broadcasters to transmit HD signals to homes as well as to mobile devices. Each broadcaster will have the flexibility to make its own decision about which services to offer on its signal.<br />
 <br />
So there is still a little ways to go but in the near future in the even of an emergency we will be able to tune into public broadcasts no matter where we are using devices we already carry. Of course you'll need one that supports mobile digital video. We have extended an invitation to the group to come on the show to talk a bit more about the subject.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May  2, 2008 10:03 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1375
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
					AND entry_id <> 1375
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_272_-_mobile_digital_tv_standard.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
