<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 560";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 560 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="video server, hard drive, mac mini, backup main, screen mode, DVD, dvd, mini, video, Mini, application, server, network, drive, mac, Mac, backup, remote, hard, need, movie, frontrow, Frontrow, DVDPedia, want" />
	<meta name="description" content="When I bought my Mac Mini last year it was done so with the sole purpose to turn it into an HTPC. But after spending a year with it, the Mini has become so much more. We primarily use the Mini as our video server. We also have an ATSC tuner (El Gato EyeTV) connected to it so it is used as a back up DVR on those rare occasions that we have to record three programs at the same time. With a little networking know-how and a couple of applications the Mini becomes quite a powerful video server.

First lets talk about the hardware..." />
	<title>HDTV Magazine Articles - Roll Your Own Video Server - Mac Mini-style</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/roll_your_own_video_server_-_mac_mini-style';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Roll Your Own Video Server - Mac Mini-style'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/03/roll_your_own_video_server_-_mac_mini-style.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Roll Your Own Video Server - Mac Mini-style</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March  9, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Entertainment">Entertainment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/03/roll_your_own_video_server_-_mac_mini-style.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/03/roll_your_own_video_server_-_mac_mini-style.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/03/roll_your_own_video_server_-_mac_mini-style.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/03/roll_your_own_video_server_-_mac_mini-style.php&amp;phase=2&amp;title=Roll%20Your%20Own%20Video%20Server%20-%20Mac%20Mini-style&amp;bodytext=When%20I%20bought%20my%20Mac%20Mini%20last%20year%20it%20was%20done%20so%20with%20the%20sole%20purpose%20to%20turn%20it%20into%20an%20HTPC.%20But%20after%20spending%20a%20year%20with%20it%2C%20the%20Mini%20has%20become%20so%20much%20more.%20We%20primarily%20use%20the%20Mini%20as%20our%20video%20server.%20We%20also%20have%20an%20ATSC%20tuner%20%28El%20Gato%20EyeTV%29%20connected%20to%20it%20so%20it%20is%20used%20as%20a%20back%20up%20DVR%20on%20those%20rare%20occasions%20that%20we%20have%20to%20record%20three%20programs%20at%20the%20same%20time.%20With%20a%20little%20networking%20know-how%20and%20a%20couple%20of%20applications%20the%20Mini%20becomes%20quite%20a%20powerful%20video%20server.%0A%0AFirst%20lets%20talk%20about%20the%20hardware...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This article is featured in the latest podcast from The HT Guys</b><br /><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/archive/2007/March09.html">http://www.htguys.com/archive/2007/March09.html</a></center>
<br />

<p>When I bought my Mac Mini last year it was done so with the sole purpose to turn it into an HTPC. But after spending a year with it, the Mini has become so much more. We primarily use the Mini as our video server. We also have an ATSC tuner (El Gato EyeTV) connected to it so it is used as a back up DVR on those rare occasions that we have to record three programs at the same time. With a little networking know-how and a couple of applications the Mini becomes quite a powerful video server.</p>

<p>First lets talk about the hardware<br /><ul><li>Apple Mac Mini 1.66GHz Intel Core Duo with 1 GB of RAM $675</li><li>NewerTech MiniStack V2 750 GB Hard Drive $450</li><li>Netgear GS608 8 port Gigabit switch $65</li></ul></p>

<p>Now we need some software to tie everything together<br /><ul><li>RemoteBuddy ($13) - With this application you won't need a mouse or Keyboard to access video playback features.</li><li>DVDPedia ($18) - An incredible application for cataloging and playing your DVDs</li><li>MacTheRipper - to backup your legally owned DVDs (this is technically not legal to do if you are breaking encryption)</li><li>Handbrake - to rip the DVD into a mp4 file (same warning applies)</li><li>VLC Media Player - the most versatile  media player on Earth</li></ul></p>

<p>Total cost for equipment and software: $1221 (This is not a bare bones system either!)</p>

<p><br />
<B>Connect the computers</B></p>

<p>The first thing you need to do is connect the computers and TVs. To have reliable video playback I chose to have a hardwired network. We did test the setup with an 802.11g network and found that DVDs played back fine on two computers simultaneously. It did slow down the wireless network for data and when my wife started surfing the Internet we noticed some slight choppiness in the video. We are confident 802.11n will work fine with multiple DVD streams and data surfing. It will probably work for HD as well depending on signal strength and network traffic. We could not get HD to work without a physical wire. With the hardwired network we were able to run three simultaneous streams of HD and surf the internet with no issues. At one point every computer on the network was watching a DVD or recorded HD TV show. The hard drive was thrashing around pretty good and I have to believe that it's not something you'll want to do often.</p>

<p>In my system I have the Mac mini with external drive connected to a Samsung DLP and three Intel based iMacs all connected via a Gigabit network. Now the external hard drive (MiniStack) connected to the Mini is done via 1394 (Firewire) so any content stored there has a maximum data rate of 400Mbps. Less than half the maximum of the network. But even with only 400Mbps max there is still enough bandwidth to support more HD streams then computers in my house.</p>

<p><br />
<B>Fill up the hard drive with content</B></p>

<p>Well now that you have the hardware in place you need to fill up the hard drive with videos. For this I use MacTheRipper and Handbrake. MacTheRipper will put an exact ISO copy of the DVD that the Apple DVD player and VLC can recognize and play. It takes about 30 minutes (depending on the speed of your Mac) to "Backup" a DVD. Each DVD runs between 5 and 9 GBs. MacTheRipper has an option to "Backup" the main feature only which reduces the size of the folder. You can use Handbrake to rip the movie into an AVI file that will play in VLC. You can setup Handbrake to extract the AC3 soundtrack so you can have multichannel sound. These files are smaller but don't look as good as the DVD. This type of rip takes twice to three times longer than the ISO "Backup". I prefer the full "Backup" of the DVD. If I run out of space I can always add an additional drive.</p>

<p><br />
<B>Accessing the content</B></p>

<p>Mac Minis come with a remote control and a software application called Frontrow. Frontrow gives the user remote control access to pretty much all the content on the Mini with a couple of exceptions. Frontrow will not access DVD files unless they are on the DVD and the DVD is inserted into the DVD slot. This drastically limits the usefulness of our Mini as a video server. There are some plugins that add this functionality to Frontrow but I have found an application that puts the Apple remote on steroids.</p>

<p>RemoteBuddy is one of the coolest applications I have found that allows you to launch applications and files and even move the mouse if needed. With this application comes support for many commonly used video applications like the Apple DVD player, VLC, EyeTV, and iTunes to name a few. You can even launch Frontrow if you like that interface. The application already knows what you want to do with these apps so the remote buttons are intuitively mapped to the most common functions. If you want to modify the remote button mapping it's a straight forward task to do so. With this application alone you can launch any video you have without need of a mouse or keyboard. So you can stop here and your done you have a Mac Mini Video server. But wait there's one more thing.<br />
 <br />
<img alt="" src="/images/articles/EyeTVRecordings.jpg" /></p>

<p><img alt="" src="/images/articles/iTunes.jpg" /></p>

<p><B>A True Media Server</B></p>

<p>Where RemoteBuddy adds functionality DVDPedia adds style! This application turns the mini into a best of breed video server. The best way to describe DVDPedia is that it's a DVD cataloging application that can do so much more. One of the nicest things about DVDPedia is how easy it is to add information about your DVD titles. It's as simple as typing in the name of the DVD and hitting enter. The application goes out and scans multiple sources including Amazon and IMDB. It comes back with the DVD cover art and everything you want to know about the DVD including credits and a description. You can even add a link to the VIDEO_TS folder or any video file and the application will launch the appropriate player when called upon.</p>

<p>In the full screen mode, which is how you want to use it when browsing on your TV, you see the DVD cover art and can scroll through all your movies with the Apple remote and when you find one that you like you simply hit a button and your movie is launched and ready to go. There are a couple of features missing in DVDPedia but they are working on them. It would be nice if the application could display multiple rows of movies. It would make navigating large libraries easier. Having a brief synopsis of the movie displayed on the screen would also be good. Currently only the title and a few other facts about the movie are displayed. The last missing feature is the ability to search based on actor, director, or studio in full screen mode would turn the Mini into an absolute killer video server. The good news is that we have been in contact with the developers and these features plus a few more are on the roadmap. Both companies are very responsive to customers and reply to emails in a timely matter.</p>

<p>My movie library consists of 50 movies and increasing every day. I have bee using MacTheRipper to "Backup" only the main feature. This has two benefits for me. First the backup takes less space on the hard drive. But what I like the most about it is that the movie starts to play immediately. No coming attractions or menu, just the full quality video that plays right away. Finally, you don't need a Mini to use DVDPedia or RemoteBuddy so while you may not want to turn your iMac or MacBook into a video server these applications will add some cool features to your computer.<br />
 <br />
<img src="/images/articles/DvdPedia.jpg" alt="" /></p>

<p><B>Not Just a Video Server</B></p>

<p>For me and many others the video serving capability alone is enough to seal the deal but that's not all I use the mini for. I also keep all my audio on the mini. With this my music is available to my Squeeze Boxes, my Xbox 360 (via connect 360), and other Macs or PCs on my network running iTunes. You can even use an Airport Express to stream music to your home theater system. The last thing I use my mini for is viewing pictures stored on my Mini. Using Frontrow or RemoteBuddy makes it a snap to select a photo album and have it displaying on screen in HD.</p>

<p>You also have the benefit of using the Mini for web browsing, email, and other computer related functions.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March  9, 2007 10:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 560
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

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on Entertainment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Entertainment'
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);
 	
 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 560
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'The HT Guys'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);
 			
 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
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
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/roll_your_own_video_server_-_mac_mini-style.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
