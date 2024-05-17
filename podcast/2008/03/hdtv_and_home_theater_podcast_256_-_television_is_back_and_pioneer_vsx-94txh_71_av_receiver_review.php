<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1291";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1291 AND placement_is_primary = 1";
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
	<meta name="keywords" content="vsx txh, auto calibration, pioneer elite, dts master, pioneer vsx, receiver, sound, Pioneer, pioneer, hdmi, txh, TXH, HDMI, video, cable, inputs, calibration, music, get, audio, remote, vsx, VSX, power, Ara" />
	<meta name="description" content="Now that the Writer's strike is over, we can finally start to get our favorite shows back.  We'll talk about when you can expect to see new episodes of the great HDTV you've been missing for the last few months.  You can read all about it at &lt;a href=&quot;http://community.tvguide.com/blog-entry/TVGuide-Editors-Blog/Ausiello-Report/Wga-Strike-Favorite/800032698&quot; title=&quot;&quot; target=&quot;_blank&quot;&gt;TV Guide&lt;/a&gt;.&amp;nbsp; We've also got a review of the Pioneer VSX-94TXH 7.1 A/V Receiver." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #256 - Television is back and Pioneer VSX-94TXH 7.1 A/V Receiver Review</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #256 - Television is back and Pioneer VSX-94TXH 7.1 A/V Receiver Review'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #256 - Television is back and Pioneer VSX-94TXH 7.1 A/V Receiver Review</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March  7, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23256%20-%20Television%20is%20back%20and%20Pioneer%20VSX-94TXH%207.1%20A%2FV%20Receiver%20Review&amp;bodytext=Now%20that%20the%20Writer%27s%20strike%20is%20over%2C%20we%20can%20finally%20start%20to%20get%20our%20favorite%20shows%20back.%20%20We%27ll%20talk%20about%20when%20you%20can%20expect%20to%20see%20new%20episodes%20of%20the%20great%20HDTV%20you%27ve%20been%20missing%20for%20the%20last%20few%20months.%20%20You%20can%20read%20all%20about%20it%20at%20%3Ca%20href%3D%22http%3A%2F%2Fcommunity.tvguide.com%2Fblog-entry%2FTVGuide-Editors-Blog%2FAusiello-Report%2FWga-Strike-Favorite%2F800032698%22%20title%3D%22%22%20target%3D%22_blank%22%3ETV%20Guide%3C%2Fa%3E.%26nbsp%3B%20We%27ve%20also%20got%20a%20review%20of%20the%20Pioneer%20VSX-94TXH%207.1%20A%2FV%20Receiver.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<p><br><br />
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-07.mp3">Listen Now - mp3</a><br />
<br><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<br><br />
<a href="http://www.htguys.com">Website</a><br />
<br><br />
<br><br />
<strong>Today's Show:</strong><br><br />
Now that the Writer's strike is over, we can finally start to get our favorite shows back.&nbsp; We'll talk about when you can expect to see new episodes of the great HDTV you've been missing for the last few months.&nbsp; You can read all about it at <a href="http://community.tvguide.com/blog-entry/TVGuide-Editors-Blog/Ausiello-Report/Wga-Strike-Favorite/800032698" title="" target="_blank">TV Guide</a>.&nbsp; We've also got a review of the Pioneer VSX-94TXH 7.1 A/V Receiver.<br />
<br><br />
<a title="Pioneer Elite VSX-94THX 7.1 Channel A/V Receiver" target="_blank" href="http://www.pioneerelectronics.com/PUSA/Products/HomeEntertainment/AV-Receivers/EliteReceivers/ci.VSX-94TXH.Kuro" id="m916">Pioneer Elite VSX-94TXH 7.1 Channel A/V Receiver</a> (MSRP $1600)<br></p>

<p>Last month Ara picked up an AppleTV for use in his media room. That became the fourth HDMI device which was one device too many for his Yamaha RX-V2700 receiver. So he contacted Pioneer to see if he could review the Pioneer VSX-94TXH 7.1 A/V Receiver. In actuality, Ara was auditioning the receiver for his own personal use. Within a few days the receiver showed up and its not going back! Just so everyone is clear, Ara has to pay for the receiver to keep it.</p>

<p><strong>Features:</strong><br />
<ul><br />
<li>HDMI<sup><font size="1">®</font></sup> 1.3a (4 inputs/1 output)</li><br />
<li>dtsHD<sup><font size="1">®</font></sup> &amp; Dolby<sup><font size="1">®</font></sup> TrueHD Decoders</li><br />
<li>DLNA<sup><font size="1">®</font></sup> Compliant Network Music and Internet Radio via I/P</li><br />
<li>Faroudja<sup>®</sup> DCDi Video Scaler</li><br />
<li>Advanced MCACC precision environment tuning -&nbsp;<em>The auto calibration is the best that we have come across. We did not need to adjust any of the settings once it was done.&nbsp;</em></li></ul></p>

<p>The VSX-94TXH is a beautiful machine albeit substantial in size and weight. The receiver weighs 41.4 lbs (18.7Kgs) and measures 16&nbsp;9/16&quot; (42 cm) x 7 3/8" (18.7 cm) x 18 1/16" (45.9 cm) (WxHxD). It has a nice piano black finish that looks great but you definitely can see finger prints. But who actually touches their equipment? The size and weight are definitely put to use producing 140 watts of power per each of its seven channels.</p>

<p>As an aside, when reading the product specs I ran across a term called Symmetrical Power Train Design. I couldn't find any documentation on what it was. But since it was in the product specification it must be important right? For nothing more than clarification we asked Pioneer what this was, this is their explanation. The Pioneer Elite A/V receivers each feature power amps for seven channels. Accurate multi-channel sound reproduction is possible only when the operating environment of one channel is physically identical to that of the others. Therefore, with the new Pioneer Elite receivers, power output devices for the left channels (front, surround and surround back) are mounted on the heat sinks symmetrically with respect to the right channels.</p>

<p><strong>Setup:</strong><br />
Setup was as straight forward as it can be when introducing a new receiver into your system. Connections were simple since most of the gear being used supported HDMI. HDMI does make life easy in cable management and universal remote programming. The only complication was using an HDMI to DVI cable between the Mac Mini and the receiver. Since the DVI cable does not carry audio, the optical output of the Mini was connected to the DVD input of the receiver. We then had to tell the 94TXH to use one of the HDMI inputs for the video. In general we like the flexibility the receiver has in assigning inputs and outputs.</p>

<p>After everything was routed we ran the Pioneer auto calibration to tune the room. Auto calibration is something most manufacturers are putting into their systems to get the best sound out of their equipment. Most mid range and up receivers have some form of auto calibration. To start the process you connect an included microphone to an input on the front of the receiver and place it in the area you want the sound optimized for. The receiver will output a series of tones and measure the response at the microphone to find the optimal settings for your room.</p>

<p>On some receivers we've felt compelled to go in and manually tweak the final results. Not so with the Pioneer, the system accurately determined the size and distance of the speakers and was able to set the gains to the proper levels. Another feature we liked was the ability to have multiple calibration profiles. Say you play your video games while sitting on the floor. You can place the microphone in that area, run the calibration and store it off in one of the receiver memory positions. If your family doesn't care so much about perfect sound you can store multiple profiles (6) for different seats in your house and use the one that is optimized for the seat you are sitting in!</p>

<p><strong>Sound:</strong><br />
We listened to the typical suite of material, compressed mp3s, music CDs, Dolby Pro Logic, Dolby Digital, and Dolby True HD. All sounded fantastic. Dialog was crisp and clear, effects were dramatic, and the LFE could shake you to the bone. Music CDs sounded great! We need to to listen to more music on CDs or at least high bit rate rips of CDs. Also, please consider that much of our listening tests are a reflection on the speakers that we use. A high quality receiver like this needs to be paired with high quality speakers to get the most out of it.</p>

<p>One feature that we found interesting is something called "Sound<br />
Retriever". This technology is supposed to bring back some of the high frequency sound that is lost&nbsp;with compressed audio. We did notice an improvement with this feature enabled but to tell you the truth, buy high bit rate music or rip them with at a minimum of 256Kbps (lossless is even better). Then you won't need technology like this and your audio will sound like it was meant to be.</p>

<p>Although there is no sound quality difference between having Dolby True HD or DTS Master Audio decoded on a receiver it was still nice to see "True HD" or "DTS Master Audio" light up in the receiver's display. However, with my Blu Ray Player, the only way to hear DTS Master Audio is to have the receiver decode it.&nbsp;The receiver definitely has the power to drive a 7.1 system and fill a large room with sound!</p>

<p><strong>Video:</strong><br />
Nowadays, receivers are also about video. The 94TXH has a Faroudja video scaler built into it and it will perform video switching for you. The issue we had with it was that receiver would not upconvert 1080i or 720p source material to 1080p. Nor would it upconvert a signal coming in over the HDMI inputs. For Ara's use scaling is adds zero value. It would have been nice to route the Xbox 360 through the receiver to have one cable going to the TV.</p>

<p>As far as switching goes, the player passed the signal through to the TV unaltered. We did not experience any HDCP issues with any of our equipment. Having four inputs makes programming the Harmony Remote a snap. I did not need to do any tweaking to get the activities to work the way I wanted.</p>

<p><strong>Odds and Ends:</strong><br />
The 94TXH has some extras the don't cost extra. It comes complete with an iPod cable which allows you to listen and watch your content on your TV through the receiver. We didn't spend too much time with this because we had a Mac Mini connected to the receiver. There is an Ethernet connection which allows for Internet radio and streaming music via and DLNA server. We couldn't find a way to do an update to the firmware via the network connection nor could we find a way &nbsp;access the settings via a web based interface. And that would be nice considering the on screen GUI looks like something that was considered good in 1985. The remote is jam packed with buttons that you may never use so your Harmony remote is almost required.</p>

<p><strong>Conclusion:</strong><br />
Overall, Ara liked the receiver enough to bump his current receiver to the family room. Sound is very good, build quality is first rate and you get an ipod connection cable included in the deal. Four HDMI inputs makes the 94TXH a very capable HDMI switch! We were a bit disappointed that the receiver would not scale a 1080i or 720p signal to 1080p and that we couldn't find a way to get our 480i signal via HDMI to scale to 1080p. The remote and on screen user interface was not worthy of an otherwise stellar product.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March  7, 2008 06:25 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1291
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
					AND entry_id <> 1291
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_256_-_television_is_back_and_pioneer_vsx-94txh_71_av_receiver_review.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
