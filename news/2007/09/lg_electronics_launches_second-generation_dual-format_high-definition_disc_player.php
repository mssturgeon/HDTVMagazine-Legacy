<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 694";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 694 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="super blu, blu player, high def, dual format, blu ray, player, blu, Blu, Player, electronics, high, disc, super, Super, Electronics, video, format, both, def, generation, available, digital, formats, second, dual" />
	<meta name="description" content="LG Electronics, the first company to introduce a dual-format high-definition disc player, today unveiled its second-generation LG Super Blu(TM) Player (model BH200). Sporting both formats' logos, LG's new high-def player is capable of Blu-ray disc and HD DVD playback with advanced BD-Java and HDi interactivity, enabling consumers to choose from the widest variety of high-def content regardless of disc format.

On display for the first time at the Custom Electronic Design and Installation Association (CEDIA) EXPO 2007 (booth #720), LG's Super Blu Player will be available to U.S. consumers and custom installers in mid-October at a suggested retail price of $999.

The BH200 further strengthens LG's position as an innovator. Seven months ago..." />
	<title>HDTV Magazine Bulletins - LG Electronics Launches Second-Generation Dual-Format High-Definition Disc Player</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('LG Electronics Launches Second-Generation Dual-Format High-Definition Disc Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/09/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">LG Electronics Launches Second-Generation Dual-Format High-Definition Disc Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  5, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray">HD DVD & Blu-ray</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/09/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/09/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player.php&amp;phase=2&amp;title=LG%20Electronics%20Launches%20Second-Generation%20Dual-Format%20High-Definition%20Disc%20Player&amp;bodytext=LG%20Electronics%2C%20the%20first%20company%20to%20introduce%20a%20dual-format%20high-definition%20disc%20player%2C%20today%20unveiled%20its%20second-generation%20LG%20Super%20Blu%28TM%29%20Player%20%28model%20BH200%29.%20Sporting%20both%20formats%27%20logos%2C%20LG%27s%20new%20high-def%20player%20is%20capable%20of%20Blu-ray%20disc%20and%20HD%20DVD%20playback%20with%20advanced%20BD-Java%20and%20HDi%20interactivity%2C%20enabling%20consumers%20to%20choose%20from%20the%20widest%20variety%20of%20high-def%20content%20regardless%20of%20disc%20format.%0A%0AOn%20display%20for%20the%20first%20time%20at%20the%20Custom%20Electronic%20Design%20and%20Installation%20Association%20%28CEDIA%29%20EXPO%202007%20%28booth%20%23720%29%2C%20LG%27s%20Super%20Blu%20Player%20will%20be%20available%20to%20U.S.%20consumers%20and%20custom%20installers%20in%20mid-October%20at%20a%20suggested%20retail%20price%20of%20%24999.%0A%0AThe%20BH200%20further%20strengthens%20LG%27s%20position%20as%20an%20innovator.%20Seven%20months%20ago...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">LG Electronics Launches Second-Generation Dual-Format High-Definition Disc Player</p>

<center><i>New Super Blu(TM) Player Offers New Features and Functionality, Continues to Provide Consumers Choice Based on Content, not Format </i></center><br />
<br />

<p><B>DENVER, Sept. 5 /PRNewswire/</B> -- LG Electronics, the first company to introduce a dual-format high-definition disc player, today unveiled its second-generation LG Super Blu(TM) Player (model BH200). Sporting both formats' logos, LG's new high-def player is capable of Blu-ray disc and HD DVD playback with advanced BD-Java and HDi interactivity, enabling consumers to choose from the widest variety of high-def content regardless of disc format.</p>

<p>On display for the first time at the Custom Electronic Design and Installation Association (CEDIA) EXPO 2007 (booth #720), LG's Super Blu Player will be available to U.S. consumers and custom installers in mid-October at a suggested retail price of $999.</p>

<p>The BH200 further strengthens LG's position as an innovator. Seven months ago, LG was the first to launch a dual-format high-def disc player, the award-winning BH100. That technological breakthrough has been widely recognized as a groundbreaking product with industry accolades including CNN's Best of CES, CNET's CES 2007 Best in Show and Home Video categories and Popular Mechanics' CES Editor's Choice Award.</p>

<p>"LG introduced the world's first high-definition dual-format player to focus on consumer needs and to end the confusion caused by the current format war," said Allan Jason, vice president of consumer electronics marketing at LG Electronics USA, Inc. "The launch of BH200 reinforces our leadership position and offers consumers and custom installers a best-in-class next-generation high-definition media player."</p>

<p>Mark Horak, executive vice president for Warner Home Video, the only major studio supporting both the HD DVD and Blu-ray disc formats, said, "The availability of movie titles on competing high-def disc formats is a challenge for both retailers and consumers. The introduction of the second-generation LG Super Blu Player addresses this challenge by fully enabling both high-def formats on one convenient player, encouraging the adoption of next-generation disc technology and growing the market."</p>

<p><br />
<B>One Player, Latest Technology</B></p>

<p>The Super Blu Player offers increased HDTV entertainment choices, because it can play 50 to 60 percent more high-def movie titles than either single-format player. With full networked interactivity available from selected HD DVDs, and the capability to handle networked "BD-Live" interactivity in forthcoming Blu-ray discs, it is the latest available technology that plays both disc formats.</p>

<p>Viewers can enjoy advanced options such as "Picture-in-Picture" available from many Blu-ray discs as well as HDi web-connected interactivity available from many HD DVDs. Access to these features enables viewing of storyboards, production videos and director's commentary as the movie is playing, as well as accessing up-to-date information. The Super Blu Player also accepts additional entertainment content via a network connection, making for an unmatched entertainment experience.</p>

<p>In addition to supporting the latest high-def disc technologies, the Super Blu Player is the ideal match for movie enthusiasts and even music enthusiasts as it supports audio CDs and the ability to up-scale standard DVDs to 1080p resolution.</p>

<p>"With its improved features and functionality, the BH200 is perfect for the custom installer in search of the next-generation player as it eliminates the need for a second player and the additional cables it requires," said Tim Alessi, director of consumer electronics marketing at LG Electronics USA, Inc. "With full support for features available on both Blu-ray discs and HD DVDs, it serves as the flagship of the LG home video product line and complements the company's expanded line-up of 'Full HD' 1080p flat-panel HDTVs."</p>

<p><br />
<B>The Ultimate Movie Viewing Experience</B></p>

<p>The LG Super Blu Player, which can output up to 1080p video resolution at 24-, 30- and 60-frames-per-second, supports various A/V formats, including MPEG-2, VC-1, H.264 video, MPEG1/2 audio, Dolby Digital, Dolby Digital+, DTS and DTS-HD audio. The unit also includes multiple inputs/outputs such as HDMI 1.3 out, component/composite video outputs, digital optical and analog audio outputs as well as a LAN Ethernet port for network interactivity.</p>

<p>LG Super Blu Player customers will enjoy superior quality video output with HDMI 1.3 Deep Color output to improve color tones and achieve finer color gradations to deliver the smoothest and most brilliant video output available. The player also features QDEO(TM) advanced video processing for superior up-scaling accuracy up to 1080p, improved noise reduction and consistent color precision.</p>

<p>The new Super Blu Player incorporates LG's SimpLink(TM) connectivity solution, enabling easier control of other LG SimpLink compatible equipment. The units are connected with an HDMI cable and use the HDMI-CEC communication standard. This allows the user to control other components on-screen or directly from each component. The BH200 also is equipped with a USB Media Host for easy access to digital music and photo libraries.</p>

<p><br />
<B>About LG Electronics USA, Inc.</B></p>

<p>LG Electronics USA, Inc., based in Englewood Cliffs, N.J., is the North American subsidiary of LG Electronics, Inc., a $48.5-billion (2006 consolidated worldwide revenues) global force in consumer electronics, home appliances and mobile communications. In the United States, LG Electronics sells a wide range of digital appliances, digital display and digital media products, and mobile phones under LG's "Life's Good" marketing theme. For more information, please visit www.LGusa.com.</p>

<p>Source: LG Electronics</p>

<p>CONTACT: John I. Taylor, +1-847-941-8181, jtaylor@lge.com, or Clara<br />
Chang, +1-201-816-2011, clarachang@lge.com, both of LG; or Greg Dawson,<br />
+1-773-972-2909, greg.dawson@ogilvypr.com, for LG</p>

<p>Web site: http://www.lgusa.com/</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  5, 2007 01:54 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 694
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
 			<h2>More on HD DVD & Blu-ray</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray'
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 694
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
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
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/lg_electronics_launches_second-generation_dual-format_high-definition_disc_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
