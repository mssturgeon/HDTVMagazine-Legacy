<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1444";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1444 AND placement_is_primary = 1";
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
	<meta name="keywords" content="iphone ipod, home theater, global cache, remote controls, network access, remote, wifi, WiFi, control, equipment, new, system, iphone, standard, commands, iPhone, network, remotes, home, could, Remote, ipod, iPod, access, systems" />
	<meta name="description" content="Isn't it annoying when you want to turn on your home theater with your IR based remote control and one or more components misses the command. It could be that someone walked in front of the remote or that you just didn't keep it pointed at your system long enough for the entire macro sequence to be transmitted. That's why most integrators shy away from IR based remotes and use systems like Crestron or Control4  to transmit commands to a head end unit which then sends the commands via Ethernet, Serial, or IR via an emitter to the equipment being controlled." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #286 - RF4CE: RF Standard for Remote Controls</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #286 - RF4CE: RF Standard for Remote Controls'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #286 - RF4CE: RF Standard for Remote Controls</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>June 22, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23286%20-%20RF4CE%3A%20RF%20Standard%20for%20Remote%20Controls&amp;bodytext=Isn%27t%20it%20annoying%20when%20you%20want%20to%20turn%20on%20your%20home%20theater%20with%20your%20IR%20based%20remote%20control%20and%20one%20or%20more%20components%20misses%20the%20command.%20It%20could%20be%20that%20someone%20walked%20in%20front%20of%20the%20remote%20or%20that%20you%20just%20didn%27t%20keep%20it%20pointed%20at%20your%20system%20long%20enough%20for%20the%20entire%20macro%20sequence%20to%20be%20transmitted.%20That%27s%20why%20most%20integrators%20shy%20away%20from%20IR%20based%20remotes%20and%20use%20systems%20like%20Crestron%20or%20Control4%20%20to%20transmit%20commands%20to%20a%20head%20end%20unit%20which%20then%20sends%20the%20commands%20via%20Ethernet%2C%20Serial%2C%20or%20IR%20via%20an%20emitter%20to%20the%20equipment%20being%20controlled.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-06-20.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Isn't it annoying when you want to turn on your home theater with your IR based remote control and one or more components misses the command. It could be that someone walked in front of the remote or that you just didn't keep it pointed at your system long enough for the entire macro sequence to be transmitted. That's why most integrators shy away from IR based remotes and use systems like <a target="_blank" href="http://www.crestron.com/">Crestron</a> or <a target="_blank" href="http://www.control4.com/">Control4</a> to transmit commands to a head end unit which then sends the commands via Ethernet, Serial, or IR via an emitter to the equipment being controlled.<br><br>
<strong>
RF4CE Consortium and a new RF Standard for Remote Controls</strong>
<br>
The RF4CE (<em>Radio Frequency for Consumer Electronics</em>) Consortium is working to develop and RF based standard for controlling your equipment. They are working an standard based on the ZigBee protocol (IEEE 802.15.5).
<br><br>
The key here is that the standard will be implemented in the components the end consumer buys (TV, Receivers, DVRs, etc) and could be easily installed by the end user. Right now to make use of this you need an custom installer to come to your home and install separate gear to receive the RF signal and then translate it into something your components understand.
<br><br>
Imagine in the future when all your gear supports this protocol. The possibilities are quite exciting:
<br><ul>
<li><strong>Clean Installations</strong> - You would be able to install your equipment in your closets or in entertainment systems behind doors. You can do this now but you need to use special equipment to eventually translate the RF commands into IR commands.</li>
<li><strong>One truly Universal Remote</strong> - You could use one remote to control your entire home. Since RF can travel a good distance and through walls, you can setup your remote with room definitions and use the settings appropriate for the room that you are in. There are IR Remotes that do this as well.</li>
<li><strong>Program Once Transfer to Many</strong> - Like cordless phones systems of today, you can add a new number in your address book and it automatically updates the other phones on the system. So too would the the new RF remotes. If you pair a new remote with your installation it can automatically receive the programming from other remotes on the system.</li>
<li><strong>WiFi/Network Access</strong> - The new remotes can be Wifi capable or a WiFi access point can be added to the network. In this mode you can control your system from a PC, WiFi enabled PDA or iPhone/iPod. There could be an application running on a server that acts as a web interface to your remote. In the case of a WiFi enabled Remote, the server would send the commands to the remote for eventual transmission to the equipment.In the case of a WiFi access point the command is sent through the network (wired or wireless) and then blasted out to the appropriate equipment. Having the WiFi/Network access could also keep the remote codes up to date.</li></ul><br>

The new specification is scheduled to be approved in the second half of 2008. No word when we can expect CE devices to support the new standard. In the mean time, if you want RF capability so that you can hide equipment behind doors, look into the <a target="_blank" href="http://www.htguys.com/shop.php?id=B000CS1TLE">Harmony 890</a>. If you want more sophisticated control talk to a custom installer.
<br><br>
If you can't wait until RF4CE products hit the market, Coming in July is a product called AirRemote. AirRemote will add WiFi control of your home theater using an iPhone or iPod Touch. The system works with your iPhone/iPod and a box made by Global Cache, which connects to your network and accepts commands from your iPhone which in turn are converted to IR or Serial. With this setup you can control your set top boxes, lighting or window shades. The system will set you back the price of your iPod/iPhone and the $125 the Global Cache box costs.
<br><br>
Theoretically the AirRemote is programmed via the iPhone and does not require the use of a professional installer. Air Remote supports AMX or Crestron automation systems. The developer says support for more devices are on the way.<br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>June 22, 2008 09:44 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1444
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
					AND entry_id <> 1444
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_286_-_rf4ce_rf_standard_for_remote_controls.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
