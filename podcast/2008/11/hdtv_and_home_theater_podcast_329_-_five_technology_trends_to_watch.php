<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1555";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1555 AND placement_is_primary = 1";
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
	<meta name="keywords" content="gesture recognition, trends watch, technology trends, sensing gesture, picture quality, control, technology, experience, home, kitchen, –, information, OLED, connectivity, recognition, oled, manufacturers, energy, Trend, displays, further, Internet, watch, next, power" />
	<meta name="description" content="Every year the Consumer Electronics Association compiles Five Technology Trends to Watch. The full document is about 25 pages long, but it has some great information in it, so we went through it to pull out the highlights. The CEA claims that the &quot;topics chosen this year by CEA market analysts cover a wide range of digital technologies that will impact the world.&quot; While they may not end world hunger or and end to war, we do agree that they are interesting trends that we all will want to keep an eye on. We'll give the HT Guys Digest version, but please read the whole article if we pique your interest. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #329 - Five Technology Trends to Watch</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #329 - Five Technology Trends to Watch'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #329 - Five Technology Trends to Watch</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>November 17, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23329%20-%20Five%20Technology%20Trends%20to%20Watch&amp;bodytext=Every%20year%20the%20Consumer%20Electronics%20Association%20compiles%20Five%20Technology%20Trends%20to%20Watch.%20The%20full%20document%20is%20about%2025%20pages%20long%2C%20but%20it%20has%20some%20great%20information%20in%20it%2C%20so%20we%20went%20through%20it%20to%20pull%20out%20the%20highlights.%20The%20CEA%20claims%20that%20the%20%22topics%20chosen%20this%20year%20by%20CEA%20market%20analysts%20cover%20a%20wide%20range%20of%20digital%20technologies%20that%20will%20impact%20the%20world.%22%20While%20they%20may%20not%20end%20world%20hunger%20or%20and%20end%20to%20war%2C%20we%20do%20agree%20that%20they%20are%20interesting%20trends%20that%20we%20all%20will%20want%20to%20keep%20an%20eye%20on.%20We%27ll%20give%20the%20HT%20Guys%20Digest%20version%2C%20but%20please%20read%20the%20whole%20article%20if%20we%20pique%20your%20interest.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-11-18.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
<strong>Five Technology Trends to Watch</strong><br><br>
Every year the Consumer Electronics Association compiles <a target="_blank" href="http://www.ce.org/PDF/2K8_5tech_WEB.pdf">Five Technology Trends to Watch</a>.  
The full document is about 25 pages long, but it has some great information in it, so we went through it to pull out the highlights.  
The CEA claims that the "topics chosen this year by CEA market analysts cover a wide range of digital technologies that will impact the 
world."  While they may not end world hunger or and end to war, we do agree that they are interesting trends that we all will want to 
keep an eye on.  We'll give the HT Guys Digest version, but please read the whole article if we pique your interest.
<br><br>
<strong>1. Control Freaks? Technologies Changing How We Interact with CE</strong><br><br>
This topic covers all of the advances we expect to see in how to control your home theater and your home, from advanced remote controls 
to whole house automation.  The topics covered include: Touch Screens, Haptics and Force Feedback, Motion Sensing and Gesture Recognition, 
Voice Activation and Speech Recognition, and Mind Control.  Some of those are pretty tame, but let's look at a few in detail.
<br><br>
<strong>Motion Sensing and Gesture Recognition</strong><br><br>
"<em>This arena of command and control is one of the most visible and talked-about in the CE universe, thanks to a confluence of R&D 
and market forces. Development efforts in motion sensing and gesture recognition are moving as fast as the gesticulations they endeavor 
to capture. Interestingly enough, the line between these approaches is beginning to blur and eventually these two control methods could 
merge.</em>"<br><br>
"<em>Most gesture recognition systems employ cameras to watch and respond to a user’s movements. Some of these cameras simply look for 
signals like an open palm or a thumbs-up. Other camera arrays bounce infrared light off the user’s hands to gauge distance from the screen 
and record hand positions in 3-D space. This approach, for instance, allows a user to tap the air to click an on-screen icon. More 
sophisticated systems in development add electronic gloves to the mix to further define and refine control inputs.</em>"<br><br>
<strong>Hold That Thought?</strong><br><br>
"<em>Imagine a PC that knows what you’re thinking. Seem like science fiction? Believe it or not three companies are deploying products 
enabling control of certain PC commands and functions just by thinking them.</em>"<br><br>
"<em>OCZ Technologies, Neurosky, and Emotiv offer headset devices that read electrical brain impulses – a process called 
electroencephalography (EEG) – and translate them into commands wirelessly transmitted to a user’s computer. The command and 
control capabilities of neural headsets are somewhat simplistic at this stage and are not intended to supplant the mouse and 
keyboard, but enhance the computing experience. A recent Forbes article cites Emotiv’s chief executive Nam Do as describing 
the technology as ‘another layer’ of control that can integrate mental responses into other kinds of interfaces. Emotiv’s 
product, called the EPOC, is expected to hit stores this fall for a price of $299.</em>"
<br><br>
In a recent CEA study, when asked what technology people would prefer to use to control a PC in the next 5 to 10 years, men responded:<br>
Keyboard and Mouse: 77%<br>
Touch Screen: 78%<br>
Voice: 69%<br>
Hand Gestures: 33%<br>
Thought: 43%<br>
<br><br>
<strong>2. Ingredients for the Kitchen of Tomorrow: Power – Connectivity – Control</strong>
<br><br>
Next up is the fully connected and automated kitchen. Now this really sounds cool, and actually quite useful in many cases.  "<em>For 
those who think kitchen technology is food processors and coffee makers, think again. The technology for the kitchen of ‘the future’ is 
available today, with everything from flatpanel displays and Internet connectivity on refrigerators to appliances that can tell you, 
or decide for you, when to run. In fact, the kitchen has the potential to become the hub for the connected home and address one of 
the most critical issues facing today’s households – the consumption and management of energy.</em>"
<br><br>
"<em>The kitchen is poised to become the front line for coordination of activities, home automation and control, and information. With 
wireless connectivity, the kitchen now can be the information hub for the home in addition to being the nutritional hub. Consumer 
electronics are pushing the envelope, enabling power management, quick and easy sharing of schedules, information organizing, entertainment 
and instructions. Consumers want access to information and control. However, which activities will drive them to incorporate this 
technology in their home?</em>"
<br><br>
<strong>3. Displays: A Look at the Next Wave of Innovation</strong>
<br><br>
<strong>Trend #1: Greater Focus on Energy Efficiency and Green Designs</strong>
<br><br>
"<em>Beyond maximizing the energy efficiency of existing technologies such as LCD or plasma, several manufacturers hope to “disrupt” 
the market with new approaches. The organic light-emitting diode (OLED) is a technology utilizing thin films of organic molecules 
that create light when hit with an electric current. In contrast to LCDs, OLED s do not require a source of backlighting and therefore 
according to OLED manufacturers, the technology consumes significantly less power (Sony claims a reduction of 40 percent power 
consumption on its 11-inch model).</em>"
<br><br>
<strong>Trend #2: Further Enhancements to the Viewing Experience and Product Design</strong>
<br><br>
Manufacturers are constantly battling to improve picture quality, but "<em>Would nearly 50 percent of U.S. households own an HDTV if 
the only upgrade was picture quality? Probably not. For many buyers, the upgrade to a sleek flat-panel display was equally as important 
as the picture upgrade. Will further reductions in the thickness of a television further entice the “small footprint” segment of consumers? 
Some manufacturers certainly hope so. As mentioned previously, OLED is banking on energy efficiency, but its value proposition also 
includes an appeal to picture quality and thinness. For example, Sony’s 11-inch OLED display measures a mere three millimeters thick.</em>"
<br><br>
<strong>Trend #3: Displays Move Beyond the Living Room</strong>
<br><br>
This section is all about putting displays in areas where you never would have expected them, the backyard, subways and subway cars, 
grocery check-out lines, gas pumps, etc.  There are all places where 10 years ago the idea of placing a TV there would have been 
crazy, but now we don't even notice them.
<br><br>
<strong>Trend #4: Connectivity Completes the 360-Degree Experience</strong>
<br><br>
"<em>During the past two International CES tradeshows, several firms unveiled televisions with built-in or add-on modules to enable 
Internet connectivity. These models work by interacting with the home’s broadband modem or wireless network, tapping into online video 
offerings. For the experimental types, this provides access to niche content, but it’s far from the mainstream experience that many 
will expect. Several firms, such as Boxee, Sezmi, or Wherever TV , seek to provide a “TV 2.0” experience as seamless as changing the 
channel on today’s television. These firms, and surely others, will incorporate interactive or social networking capabilities into 
their platforms, offering the possibility of transforming the passive television experience into a communal affair. While the DVR has 
transformed the television experience for nearly 4 in 10 consumers, the time-shifting technology stops short of providing a true 
on-demand viewing experience. The Sezmi set-top box, which boasts 1 terabyte of storage, markets itself as “optimized for on-demand 
viewing,” a clear leap over DVR functionality. True, comprehensive on-demand viewing may be some years off, yet the pieces are slowly 
falling into place for this to become a reality.</em>"
<br><br>
<strong>4. The Future has Already Arrived: The Localization of the Internet</strong>
<br><br>
"<em>Joe Modern pulls into the parking garage—after using his in-dash GPS unit to guide him— and enters the shopping mall. He is 
catching a matinee of the movie his friend recommended, e-mailing the review to his smartphone. He pulls up the movie theater website 
to confirm the starting time of the film, and receives a text message with a menu and coupon for one of the restaurants in the food 
court. He accepts the text, and suddenly there are several more offers, with coupons, advertising other appealing options. Before 
he knows it, he has so many enticing offers that he is not even sure he still wants to see the movie!</em>"
<br><br>
<strong>5. The Contextual Web</strong>
<br><br>
"<em>In this next era of the Internet, devices will do much of the sorting, filtering, contextualizing and connecting of data currently 
done by individuals. Presently, most of our technology experiences require significant user involvement. We tell the Web browser where 
to go then are subsequently required to parse the abundance of information retrieved. We segment and isolate content across software 
applications – from calendars and photo-sharing services to social networks and music subscriptions.</em>"
<br><br>
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>November 17, 2008 11:07 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1555
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
					AND entry_id <> 1555
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
					FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
					WHERE
						t.forum_id = af.forum_id
						AND af.exclude_general = 0
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_329_-_five_technology_trends_to_watch.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
