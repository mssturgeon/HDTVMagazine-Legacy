<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 588";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 588 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dvd player, atsc tuner, upconverting dvd, dvd recorder, panasonic dmr, DVD, dvd, player, quality, digital, panasonic, recorded, DMR, video, dmr, Panasonic, ATSC, playback, atsc, recording, upconverting, control, mode, divx, vhs" />
	<meta name="description" content="The Panasonic DMR-EZ47VK can be thought of as a digital video Swiss Army knife. It has a built in ATSC tuner, DVD recording capability, VHS Playback and dubbing, and its an upconverting DVD player (up to 1080p via HDMI) to boot. So with all this capability we were excited when the unit showed up at the home offices of the HT Guys. On paper this device was too good to be true. So how did  it fare in the real world?
" />
	<title>HDTV Magazine Reviews - Panasonic DMR-EZ47VK DVD Recorder</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_dmr-ez47vk_dvd_recorder';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic DMR-EZ47VK DVD Recorder'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_dmr-ez47vk_dvd_recorder.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic DMR-EZ47VK DVD Recorder</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May  3, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Upconverting DVD Players">Upconverting DVD Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_dmr-ez47vk_dvd_recorder.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2007/05/panasonic_dmr-ez47vk_dvd_recorder.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_dmr-ez47vk_dvd_recorder.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_dmr-ez47vk_dvd_recorder.php&amp;phase=2&amp;title=Panasonic%20DMR-EZ47VK%20DVD%20Recorder&amp;bodytext=The%20Panasonic%20DMR-EZ47VK%20can%20be%20thought%20of%20as%20a%20digital%20video%20Swiss%20Army%20knife.%20It%20has%20a%20built%20in%20ATSC%20tuner%2C%20DVD%20recording%20capability%2C%20VHS%20Playback%20and%20dubbing%2C%20and%20its%20an%20upconverting%20DVD%20player%20%28up%20to%201080p%20via%20HDMI%29%20to%20boot.%20So%20with%20all%20this%20capability%20we%20were%20excited%20when%20the%20unit%20showed%20up%20at%20the%20home%20offices%20of%20the%20HT%20Guys.%20On%20paper%20this%20device%20was%20too%20good%20to%20be%20true.%20So%20how%20did%20%20it%20fare%20in%20the%20real%20world%3F%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2007/May04.html">http://www.htguys.com/archive/2007/May04.html</a></center>
<br />

<p>The <a href="http://www2.panasonic.com/webapp/wcs/stores/servlet/vModelDetail?storeId=15001&catalogId=13401&itemId=112150&catGroupId=24987&modelNo=DMR-EZ47VK&surfModel=DMR-EZ47VK&cacheProgram=11002&cachePartner=7000000000000005702">Panasonic DMR-EZ47VK</a> ($330 <a href="http://www.htguys.com/shop.php?id=B000O3LUKC">Buy Now</a>) can be thought of as a digital video Swiss Army knife. It has a built in ATSC tuner, DVD recording capability, VHS Playback and dubbing, and its an upconverting DVD player (up to 1080p via HDMI) to boot. So with all this capability we were excited when the unit showed up at the home offices of the HT Guys. On paper this device was too good to be true. So how did  it fare in the real world?</p>

<p><em>ATSC Tuner</em><br />
The unit comes with a built in ATSC tuner which will make your current TV ready for the analog cutoff. That is if over the air is how you are getting your current TV signals. More likely its not but its still nice to know that you can record those beautiful digital signals in their pristine quality. However, while the player is able pick up the digital HDTV signal it has no way to record them in 720p or 1080i. But what you get is a very clean 480p (DVD quality) signal recorded to a DVD. An area of disappointment though is if you are want this device to act as an HDTV source for your HD Ready TV you are out of luck. The player won't pass an HD signal through to the TV. We thought it was odd that the ATSC tuner does not support an Electronic Program Guide. Although the interface is straight forward to use, it makes setting up recordings a bit more like the old VCRs. You really never know you recorded what you want until you see it on the screen.</p>

<p><em>DVD Playback (Upconverting Performance)</em><br />
We evaluated the picture with both objective and subjective tests. For the objective tests we used the HQV DVD from Silicon Optix. We connected the player to our Mitsubishi WD-65831 and set the player to output 1080p via the HDMI cable. We found that the player did a great job with the Color Bar test and a decent job with the Jaggies. The jaggies test evaluates the players capabilities of dealing with moving objects. Detail Enhancement, Noise Reduction and Motion Adaptive tests were a bit too difficult for this player but it did a great job with 3:2 detection. Overall the DMR-EZ47VK is a decent upconverting DVD player. Definitely an improvement over a standard DVD player.</p>

<p>Because the HQV disc is set up to stress the DVD players capabilities, we also wanted to take a look at how this player performed with regular movies.  Here we pulled out our old standbys, Blackhawk Down and Spiderman 2 Superbit. The player did an excellent job with these movies. The night scope vision scenes did not appear grainy and detail was highly visible in dark scenes. Spiderman 2 looked incredible! We can't wait for the Spiderman 3. Digital audio, both Dolby Digital and DTS, was transmitted via the HDMI cable. So one cable brought both video and audio to the receiver. The whole chain, (Mitsubishi DLP, Panasonic DVD Recorder, and Aperion Speakers) looked and sounded fantastic. We forgot that the player was a DVD Recorder with upconversion capability. Overall, we were very pleased with the playback of DVDs on the DMR-EZ47VK.</p>

<p><em>VHS Playback and Dubbing</em><br />
If you have boxes of old videos that you recorded of the kids and are looking for a way to transfer and store them in a digital format then this product if for you. Saving your child's school play for future generations is as simple as inserting the video tape and blank DVD-R and hitting a single button. This also works for copying DVDs to a blank VHS Tape. Can you still find those in stores ;-) ? The recorder supports almost all recordable disc formats including dual layer discs.</p>

<p><em>DVD Recording</em><br />
The player has four recording modes that come with a good mix of capacity to quality considerations. The highest quality mode (XP) can record one hour of video on a single layer disc. The longest record times are achieved in EP mode with up to 8 hours of recording. Dual-layer discs are supported and almost double these values. The unit also supports SP and LP modes. All recordings can be watched on the player via a "Recordings" menu item that has a thumbnail interface with motion. To watch your recordings on a standard DVD player the disc needs to be finalized which can take up to fifteen minutes. Something to consider when playing back a widescreen recording on the DMR-EZ47VK is the image is displayed in the proper aspect ratio. But when you play it back on another upconverting DVD player the image is squished to a 4:3 aspect ratio. You would then need to adjust your TVs settings to stretch the picture back to 16:9.</p>

<p>Video quality is is a function of the recording mode. In the longest playing mode (EP) the quality is quite degraded as compared to XP mode. Recorded HD looked OK but it was easy to see that the picture was not HD. If you are used to watching HD you will see a definite quality loss in what you are used to watching. But with that said, the digital picture was better than any SD picture we could come up with. Dubbed VHS tapes looked as good as they could considering the source.</p>

<p><em>Other Considerations</em><br />
The player has a SD slot but its only good for viewing pictures. The DVD player supports DIVX. From the Panasonic Website: Official DivX Certified product. Plays all versions of DivX video (including DivX6) with standard playback of DivX media files. The player will also playback CD Audio and mp3s. And finally, the unit supports EZ Sync HDAVI Control so you can connect your DVD player to your Panasonic components and control them with one touch of your remote control. The last thing we want to cover is the menu system and remote control. The Menu system is a bit complex and not intuitive to traverse. The remote had allot of buttons and you really had to search for the buttons you needed. But its understandable when you consider how many functions this product does. We just wanted to point out that you will need to spend some time with the player to familiarize yourself with it to get the most out of it.</p>

<p><strong>What we liked:</strong><ul><li>ATSC tuner</li><li>Upconversion to 1080p via HDMI</li><li>One touch dubbing of old VHS tapes</li><li>Supports just about any type of DVD recordable media</li></ul></p>

<p><br />
<strong>What we disliked:</strong><ul><li>ATSC tuner does not pass HD signals</li><li>No EPG guide</li><li>Widescreen recorded programs are recorded as 4:3 (when played back on other upconverting DVD players)</li><li>Complex Menu  and remote control </li></ul></p>

<p><br />
<em>Conclusion:</em><br />
The Panasonic DMR-EZ47VK is the Swiss Army knife of DVD players. When you consider all the functions that it performs, the $330 price tag is well worth it.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May  3, 2007 10:51 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 588
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
 				AND entry_id <> 588
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_dmr-ez47vk_dvd_recorder.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
