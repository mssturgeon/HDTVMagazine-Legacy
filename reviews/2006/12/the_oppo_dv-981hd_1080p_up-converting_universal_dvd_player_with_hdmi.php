<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 492";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 492 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (8) {
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
	<meta name="keywords" content="dvd player, bend beckham, does support, aquamarine bend, support hdcp, player, DVD, dvd, Oppo, oppo, video, audio, buy, good, divx, DivX, make, well, improvement, standard, Audio, both, players, support, universal" />
	<meta name="description" content="This review is featured in the latest podcast from The HT Guyshttp://www.htguys.com/archive/2006/December222006.html We reviewed the Oppo 970 and 971 back in August (Podcast 98) of this year. At that time we said that it didn't not make sense to buy another DVD player. That was before the DV-981HD ($229 MSRP, Buy now) was released. But with three DVD players on the market which Oppo is right for you. We'll tackle that question later. The 981 will upconvert standard DVDs to..." />
	<title>HDTV Magazine Reviews - The Oppo DV-981HD 1080p Up-Converting Universal DVD Player with HDMI</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The Oppo DV-981HD 1080p Up-Converting Universal DVD Player with HDMI'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2006/12/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The Oppo DV-981HD 1080p Up-Converting Universal DVD Player with HDMI</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December 22, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Upconverting DVD Players">Upconverting DVD Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2006/12/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2006/12/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2006/12/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2006/12/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi.php&amp;phase=2&amp;title=The%20Oppo%20DV-981HD%201080p%20Up-Converting%20Universal%20DVD%20Player%20with%20HDMI&amp;bodytext=This%20review%20is%20featured%20in%20the%20latest%20podcast%20from%20The%20HT%20Guyshttp%3A%2F%2Fwww.htguys.com%2Farchive%2F2006%2FDecember222006.html%20We%20reviewed%20the%20Oppo%20970%20and%20971%20back%20in%20August%20%28Podcast%2098%29%20of%20this%20year.%20At%20that%20time%20we%20said%20that%20it%20didn%27t%20not%20make%20sense%20to%20buy%20another%20DVD%20player.%20That%20was%20before%20the%20DV-981HD%20%28%24229%20MSRP%2C%20Buy%20now%29%20was%20released.%20But%20with%20three%20DVD%20players%20on%20the%20market%20which%20Oppo%20is%20right%20for%20you.%20We%27ll%20tackle%20that%20question%20later.%20The%20981%20will%20upconvert%20standard%20DVDs%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2006/December222006.html">http://www.htguys.com/archive/2006/December222006.html</a></center>
<br />

<p>We reviewed the Oppo 970 and 971 back in August (<a href="http://www.htguys.com/archive/2006/August252006.html">Podcast 98</a>) of this year. At that time we said that it didn't not make sense to buy another DVD player. That was before the <a href="http://www.oppodigital.com/dv981hd/index.html">DV-981HD</a> ($229 MSRP, <a href="http://www.oppodigital.com/?partner=825">Buy now</a>) was released. But with three DVD players on the market which Oppo is right for you. We'll tackle that question later. The 981 will upconvert standard DVDs to 408p, 720p, 1080i, and 108p. DCDi by Faroudja video processing technology with progressive scan, film mode detection, TrueLife enhancement and motion adaptive noise reduction. The player supports both NTSC and PAL formats. This player does not have component connections. If you are going to watch on an HDTV you will need to use a digital connection.</p>

<p><strong>Features:</strong><br />
<ul><li>DVD-Audio, Super Audio CD (SACD) and DVD-Video universal playback</li><li>Compatible with Audio CD, HDCD, WMA, Kodak Picture CD, and other digital audio/video/picture media and formats</li><li>Official DivX Certified product, certified to the Home Theater Profile</li><li>Plays all versions of DivX video (including DivX 6) with standard playback of DivX  media files</li><li>Plays XviD and .SRT, .SMI, .IDX and .SUB format</li></ul></p>

<p>Like our last review we tested the 981 out of the box without any changes to the settings on the player. We tested the player on a 720p and 1080p TV. There are a lot of settings that can be adjusted to get the most out of the player. In our opinion you shouldn't need to adjust these unless you want to have you HDMI input calibrated for one device and have your Oppo player calibrated differently. Both our test TVs were calibrated properly and we found the the DVD player at its default settings produced the best picture for us.</p>

<p>The Oppo made a big improvement on both TVs. But we have to make it clear that if your TV does not support HDCP you won't see anything. You'll see a boot up screen for a few seconds and then black. The first TV we tried to test it on did not work. The tech support at Oppo was was quick in the troubleshooting of this problem. In general we have found the people at Oppo very supportive of their products.</p>

<p>We watched Spiderman II (Superbit), Blackhawk Down, Tripple X, Aquamarine, and Bend it like Beckham. All but Bend it like Beckham showed great improvement on the 720p TV. Spiderman showed the best improvement. In general the better the DVD quality was on a regular DVD player the greater the improvement was using this player. Last week we watched the standard DVD Blackhawk Down on the SONY Blu Ray player. We mentioned that it did not do well with scenes simulating night vision. The 981 breezed through these scenes. We were surprised with the playback of Aquamarine, it was quite good. It was a little different when we connected it to a 1080p TV. We either saw even more improvement or more noise. On the Superbit version of Spiderman II we were blown away by how good it looked. Likewise we were impressed with Aquamarine. Bend it like Beckham showed a lot of noise especially around faces and in soccer scenes. We stopped the video and changed the output of the player to 720p and then started the player again. Note - you can not change the output settings on the fly. You have to start and stop playback to do this. At 720p we noticed that most of the noise was gone.</p>

<p>When compared playback on a regular DVD player side by side we noticed that the colors seemed more natural on the Oppo. Especially the skin tones. This was true of the 970 and 971 players as well. In Blackhawk down we noticed that the 981 was able to provide more detail than the standard DVD player. A couple of points that we made in our previous review still stand with the 981. One, if the DVD you are using is not good to begin with this player will not turn it into something you can compare with HD. Second if the DVD is already good this player will make it much better. Poor DVDs looked worse on a 1080p than a 720p TV. The opposite was true as well.</p>

<p>Since Oppo makes three players we thought it would be good to make recommendations on which one you should buy;<br />
<ul><ol><li>If you own a 1080p TV - DV-981HD</li><li>If you own a screen greater than or equal to 50 inches - DV-971HD or DV-981HD</li><li>If you own a screen smaller than 50 inches - DV-970HD</li><li>If you are going to use an external video processor like the DVDO VP30 -  DV-970HD set to 480i or 576i</li></ol></ul></p>

<p><strong>This and That:</strong><br />
The menus are basic and easy to navigate. The preference panel is only accessible if the player is completely stopped. The remote control is basic but has some dedicated keys that will make switching resolution easy. If you opt for a universal remote these commands will need to be mapped to a soft key. The 981 has a universal power supply and supports both NTSC and PAL so you should be able to use this player anywhere in the world. The player is also region free so you can watch DVDs from anywhere as well. It comes with an HDMI cable too!</p>

<p><strong>What we liked:</strong><ul><li>1080p upconversion!</li><li>Plays about every format under the sun</li><li>DCDi by Faroudja</li><li>Nice looking well built hardware</li></ul></p>

<p><strong>What we disliked:</strong><ul><li>No error message when connecting to a TV that does not support HDCP</li></ul></p>

<p><strong>Bottom Line</strong><br />
At $229 you can to buy a cheaper DVD player. But not one that will support more audio and video formats (DVD Video, DVD Audio, SACD, DivX, XviD, Audio CD, HDCD, WMA, DVD+R/RW, DVD-R/RW, CD-R/RW, and Kodak Picture). If you have a HDTV you should have one of these DVD players in your home theater. If you have a 1080p TV and you want to wait a until the format war is over you will want to buy DV-981HD. This player will breath new life into your standard DVDs. We also got a hold of a SACD (Dark Side of the Moon) to listen to. It sounded phenomenal! As if we need another reason to go buy more music!</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December 22, 2006 07:22 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 492
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
 			<h2>More on Upconverting DVD Players</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Upconverting DVD Players'
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
			
 		<?if (8 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 8
 				AND entry_id <> 492
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
 				<h2>About Reviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2006/12/the_oppo_dv-981hd_1080p_up-converting_universal_dvd_player_with_hdmi.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
