<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1389";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1389 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, motion enhancer, inch lcd, cox cable, our friend, picture, settings, looked, our, blu, ray, Blu, Ray, color, sony, enhancer, Color, good, Cox, better, cox, calibration, Our, friend, LCD" />
	<meta name="description" content="As you would imagine being a close friend of the HT Guys comes with some benefits. We have a friend that just bought the SONY KDLXBR4 46 inch LCD and a SONY Blu Ray player. He asked for some help setting the TV up and of course we said yes. Well, we said yes because it provides good material for our show. Our friend uses Cox cable, OTA and Blu Ray for his HD material." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #274 - Sony KDL46XBR4 46 inch LCD HDTV</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #274 - Sony KDL46XBR4 46 inch LCD HDTV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #274 - Sony KDL46XBR4 46 inch LCD HDTV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May  8, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23274%20-%20Sony%20KDL46XBR4%2046%20inch%20LCD%20HDTV&amp;bodytext=As%20you%20would%20imagine%20being%20a%20close%20friend%20of%20the%20HT%20Guys%20comes%20with%20some%20benefits.%20We%20have%20a%20friend%20that%20just%20bought%20the%20SONY%20KDLXBR4%2046%20inch%20LCD%20and%20a%20SONY%20Blu%20Ray%20player.%20He%20asked%20for%20some%20help%20setting%20the%20TV%20up%20and%20of%20course%20we%20said%20yes.%20Well%2C%20we%20said%20yes%20because%20it%20provides%20good%20material%20for%20our%20show.%20Our%20friend%20uses%20Cox%20cable%2C%20OTA%20and%20Blu%20Ray%20for%20his%20HD%20material.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-09.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
<strong>2008 Electronic House of the Year</strong><br />
 <br />
Talk about dreaming. Today we talk about some of the homes that made the <a target="_blank" href="http://www.electronichouse.com/specials/hoty08">Electronic House of the year list for 2008</a>. These home are amazing and make us drool just looking at the pictures. This is a highly visual feature and you really have to you to the site and follow along with us. We won't be talking about all the homes on the list. There are 14 categories and a Gold, Silver, and Bronze winner in each.</p>

<p><strong><a target="_blank" href="http://www.sonystyle.com/webapp/wcs/stores/servlet/ProductDisplay?catalogId=10551&storeId=10151&langId=-1&productId=8198552921665116636">Sony KDL46XBR4 46 inch LCD HDTV</a> (MSRP $3300 <a target="_blank" href="http://www.htguys.com/shop.php?id=B000UN8MKM">HT Guys Store $2777</a>)</strong><br />
As you would imagine being a close friend of the HT Guys comes with some benefits. We have a friend that just bought the SONY KDLXBR4 46 inch LCD and a SONY Blu Ray player. He asked for some help setting the TV up and of course we said yes. Well, we said yes because it provides good material for our show. Our friend uses Cox cable, OTA and Blu Ray for his HD material.<br />
 <br />
<strong>Features:</strong><ul><li>1080P</li><li>10-bit Processing and 10-bit Display</li><li>Motionflow™ 120 Hz with Full HD high frame rate capability</li><li>Deep Color Support</li><li>24p True Cinema (24p Input Capability)</li><li>DMex - Ready (Digital Media Extender) - <em>Sony's Digital Media Extender (DMex) ready televisions offer a digital connection path for the addition of the optional modules like the new BRAVIA Internet Video Link. 6 With innovative DMex expansion capabilities featuring the Emmy® award winning XMB user interface, these models are not merely TVs, but powerful entertainment platforms that not only meet your needs today, but extend to add new features seamlessly.</em></li></ul></p>

<p><strong>Impression:</strong><br />
The TV looks visually appealing and has a high build quality. There is a clear plastic frame that goes around the the TV. The TV weighs 84 pounds (38 Kgs) with the pedestal and measures 49.7" (126 cm) wide by 31.3" (79.5 cm) high by 17.7" (32 cm) deep (4.8" (12 cm) without the pedestal). We immediately took the TV off the default setting and changed it to cinema. Cox cable looked very bad. So bad that we recommended switching to satellite. Braden has Cox as one of his sources so his impression was that Cox does not look that bad on his TVs. It could be that this TV does not do well with compressed sources. OTA HD looked much better. In general OTA digital channels looked better than the SD coming from the Cox cable box.</p>

<p>We then popped in a Blu Ray disc (Fantastic Four Rise of the Silver Surfer) and were quite impressed with the picture. We saw great detail in the picture and found that the skin tones looked very natural. Color representation was highly accurate. The TV has very good black levels for a an LCD. We found that computer generated scenes looked fake for some reason. The skiing scene looked very good. There was a lot of contrast between the white snow and the blue sky. The TV has a Contrast Ratio of 2,000:1 We liked the off angle viewing of this TV. While not on par with Plasmas its much better than most LCDs we've seen.</p>

<p>Next we started playing around with the settings to dial the TV in. Our friend was happy with the pre-configured cinema settings and was getting antsy about us spending so much time at with his TV. In the end our quick calibration made the picture even better. Our calibration settings are included at the end of this review. Please use them as a starting point only. We ended up turning the all the special processing off. You can play around with those to see if you like what you see. With HD film based material we felt the Motion Enhancer took away from the viewing experience. With SD material it actually improved the picture. This is very subjective so if you plan on buying this TV or you already own one experiment with these settings.</p>

<p>Once we had it dialed in we went back and watched the Fantastic Four Blu Ray Disc. It was clear to all that we had indeed improved the picture. Our friend was happy we came by. It was everything we said before about the clarity, color, and detail but improved. We also watched Standard Definition DVDs (Spider Man 3, 27 Dresses, and Enchanted) after calibration. We were pleasantly surprised at how good the picture looked. The motion enhancer actually improved the picture. Our recommendation is screen parts of the movie with the motion enhancer on and then turn it off. See which one you prefer. You'll soon figure out what types of movies this will help and what types it won't help. Unfortunately we do not have a clear cut answer for its use.</p>

<p><strong>Conclusion:</strong><br />
Overall, the SONY makes for a nice TV when a  good quality HD signal is present. If you have an overly compressed signal via OTA or Cable you won't be happy with what you see. To really show off this set you should also consider buying a Blu Ray player. Please do not leave the set in its default settings. At a minimum switch it to Cinema mode. Better yet calibrate it with any of the calibration DVDs or have a professional do it for you. This TV will really show its stuff when set up properly. The only negatives we can find are Its Optical Output does not 5.1 for anything other than its ATSC tuner (but this is the case with almost all TVs) and its a bit on the pricey side.</p>

<p><strong>Calibration Settings</strong> (Use only as a starting point):<br />
HDMI Settings (Blu Ray)</p>

<p>Picture Mode: Custom<br />
Backlight: 3<br />
Picture: 66<br />
Brightness: 45<br />
Color: 57<br />
Hue: 0<br />
Color Temperature: Neutral<br />
Sharpness: 38<br />
Noise Reduction: Off</p>

<p>Advanced Settings<br />
White Balance:<br />
R-Gain: 0<br />
G-Gain: -5<br />
B-Gain: -5<br />
R-Bias: 0<br />
G-Bias: -5<br />
B-Bias: 0</p>

<p>Color Space: Standard<br />
Detail Enhancer: off<br />
Edge Enhancer: off<br />
Everything else set to off<br />
Motion Enhancer: off<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May  8, 2008 11:10 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1389
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
					AND entry_id <> 1389
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_274_-_sony_kdl46xbr4_46_inch_lcd_hdtv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
