<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 578";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 578 AND placement_is_primary = 1";
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
	<meta name="keywords" content="noise reduction, video noise, independently separately, light engine, separate colors, mitsubishi, Mitsubishi, video, good, screen, color, calibration, dlp, settings, DLP, noise, our, colors, picture, Reduction, contrast, digital, detail, reduction, Ara" />
	<meta name="description" content="If you have listened to this show for more than a couple of months you know that Ara has purchased a 65 inch Mitsubishi DLP WD-65831 $2950 online. Many will remember the journey that got him there. He looked at the SONY SXRD, JVC HD-ILA, and he even toyed with buying the Samsung LED based DLP. In the end, one Saturday afternoon while hanging out in a Magnolia store he saw the Mitsubishi and decided it was that TV that was going into the Media Room.
" />
	<title>HDTV Magazine Reviews - Mitsubishi 65 inch 1080p DLP (WD-65831)</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/mitsubishi_65_inch_1080p_dlp_wd-65831';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Mitsubishi 65 inch 1080p DLP (WD-65831)'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi_65_inch_1080p_dlp_wd-65831.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Mitsubishi 65 inch 1080p DLP (WD-65831)</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 17, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HDTV Displays">HDTV Displays</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi_65_inch_1080p_dlp_wd-65831.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi_65_inch_1080p_dlp_wd-65831.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi_65_inch_1080p_dlp_wd-65831.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi_65_inch_1080p_dlp_wd-65831.php&amp;phase=2&amp;title=Mitsubishi%2065%20inch%201080p%20DLP%20%28WD-65831%29&amp;bodytext=If%20you%20have%20listened%20to%20this%20show%20for%20more%20than%20a%20couple%20of%20months%20you%20know%20that%20Ara%20has%20purchased%20a%2065%20inch%20Mitsubishi%20DLP%20WD-65831%20%242950%20online.%20Many%20will%20remember%20the%20journey%20that%20got%20him%20there.%20He%20looked%20at%20the%20SONY%20SXRD%2C%20JVC%20HD-ILA%2C%20and%20he%20even%20toyed%20with%20buying%20the%20Samsung%20LED%20based%20DLP.%20In%20the%20end%2C%20one%20Saturday%20afternoon%20while%20hanging%20out%20in%20a%20Magnolia%20store%20he%20saw%20the%20Mitsubishi%20and%20decided%20it%20was%20that%20TV%20that%20was%20going%20into%20the%20Media%20Room.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2007/April20.html">http://www.htguys.com/archive/2007/April20.html</a></center>
<br />

<p>If you have listened to this show for more than a couple of months you know that Ara has purchased a 65 inch <a href="http://www.mitsubishi-tv.com/j/i/18326/WD65831.html?cid=524">Mitsubishi DLP WD-65831</a> $2950 online (<a href="http://www.htguys.com/shop.php?id=B000JLCTC2">Buy Now</a>). Many will remember the journey that got him there. He looked at the SONY SXRD, JVC HD-ILA, and he even toyed with buying the Samsung LED based DLP. In the end, one Saturday afternoon while hanging out in a Magnolia store he saw the Mitsubishi and decided it was that TV that was going into the Media Room.<br />
<strong><br />
Description</strong><br />
This is a 1080p TV with two HDMI inputs that accept 1080p picture sources. In addition to the HDMI inputs the TV also supports PC DVI-I (1), Component (3), S-Video, RCA and IEEE1394 (Front and Back). The TV has 6-Color Light Engine and Mitsubishi's own TurboLight180 lamp system that is supposed to provided high detail and bright vivid colors. You can adjust the intensity and tint of each color independently and separately for each input providing ultimate calibration options. The TV is also CableCard ready so you won't need a digital set top box if your cable provider supports the technology. The TV also has a memory card reader for viewing your digital images on the screen. Finally, the TV ships with two remote controls. One that is full featured and a second simple remote that only include the most common control functions.</p>

<p>The TV is large (40 3/4 x 58 1/2 x 19 13/16  99.0 lbs.). In fact, Ara's wife was ready to send it back when it showed up. Oddly enough, after about a month and a half of use, it no longer feels so large. With that said, when you consider that the TV has a 65 inch screen and is just under 20 inches deep its not as big as you think it would be. The TV is aesthetically pleasing. It has a piano black finish with a thin bezel that barely frames the screen. The matching base completes the look and is quite functional. One note of warning. Be prepared to dust the TV often as the black will show dust. The TV fits on the stand nicely and both complement each other. The stand easily will support your electronics and center channel speaker. Cable management is easy as well.</p>

<p>Features (from Mitsubishi's Website):<ul><li>1080P DLP Display</li><li>Mitsubishi Exclusive 6-Color Light Engine - generates yellow, cyan and magenta directly for brighter colors, a wider range of colors and whiter whites.</li><li>TurboLight180 - Our unique, patented optical design focuses light more efficiently to produce a 10% brighter on-screen image.</li><li>High Contrast Picture</li><li>Plush1080p - Mitsubishi's video scaling technology</li><li>Tru1080p Processing</li><li>4D Video Noise Reduction - Mitsubishi’s exclusive 4D Video Noise Reduction uses advanced algorithms to better identify video noise from fine detail and correct the signal rather than distort it.</li><li>PerfectColor - ability to adjust the intensity of six separate colors independently of each other and separately for every input.</li><li>PerfecTint - ability to adjust the tint of six separate colors independently of each other and separately for every input.</li><li>DeepField Imager - constantly adjusts brightness and contrast for optimum settings in all areas of the picture.</li><li>SharpEdge - It enhances horizontal and vertical edges for stunning picture precision</li><li>Video Modes: Brilliant / Bright / Natural</li></ul></p>

<p><br />
<strong>Setup</strong><br />
Hooking the TV was straight forward. We connected the antenna to the digital tuner and and ran our digital inputs (Satellite TV and Up-converting DVD player) through our switching receiver and then into the HDMI input. Once setup we scanned the digital airwaves and we were ready to go. We used the DVE to calibrate the TV (see the settings at the end of this writeup). But that was just our starting point. We continued to tweak the picture until we had it just so. There are settings on the TV like Video Noise Reduction, Sharp Edge, and Deep Field that we just turned on and off to see if we liked the results. For the record we turned Noise Reduction and Sharp Edge off. We left Deep field on.</p>

<p><strong>Performance:</strong><br />
The first thing we have to say about this TV is that you need to be prepared to spend some time with it calibrating it. We typically say you need to take any TV you buy off its default settings  to get a good picture, and that is true about this TV as well. However, if that is all you do with this TV you will really be missing out (not to mention spending too much money for the TV). With this TV we strongly recommend doing a full calibration or hiring an ISF certified professional to do it for you. Only then will it perform to its fullest capability. For reference purposes we are including the calibration settings we used for this review at the end of this review.</p>

<p>The 831 has some of the deepest blacks we have seen on a TV that isn't a plasma, it has good color representation (after the Perfect Color and Perfect Tint adjustments), and great detail in dark scenes. Watching HD is like looking through your perfectly cleaned window. The detail is amazing. Standard Definition looks pretty good but with such a large screen its easy to see the flaws in the picture. The speakers on the TV do a good job and sound surprisingly full. But to be honest with you after the initial listen they have not been turned on since.</p>

<p>No (virtually no) rainbows! One of the issues with DLP TVs is an something  known as Rainbows. From Wikipedia: The DLP "Rainbow Effect" This visual artifact is best described as brief flashes of perceived red, blue, and green "shadows" observed most often when the projected content features bright/white objects on a mostly dark/black background (the scrolling end credits of many movies are a common example). In the month and a half Ara has had this TV he has only seen two rainbows and they were barely perceivable. In contrast, on his his second generation DLP, Ara sees rainbows all the time.</p>

<p>Another issue that affects Rear Projection TVs is something known as Silk Screen Effect (SSE). Some viewers can see the texture of the screen in front of the image. Its pretty bad on the default settings. It can be minimized and almost eliminated by properly setting you contrast and brightness. With that said it is still noticeable under certain viewing conditions. In Ara's case it is most noticeable when watching hockey and specifically when looking at the ice. But after calibration it has not detracted from the overall look of the game.</p>

<p>One thing to consider with DLPs is that their off angle viewing is not as good as plasmas and some newer LCDs. The Mitsubishi does a good job at off angle viewing and not so good with vertical angles. That is to say if you play video games standing up this TV is not for you. For typical TV and movie watching there will be no issues for almost anyone sitting in front of the TV. If you have some seats way off to the side (beyond 145 degrees) your viewing experience will start to degrade.</p>

<p>Some have complained about the fan noise of this TV. We measured it with a Sound Pressure Meter right at the source of the fan and got a reading of 65 dB. That's like putting your ear at the exhaust fan of the TV.  To put this in perspective 65 dB is right between clothes dryer (60 dB) and a Vacuum Cleaner (70 dB). At a normal distance of 14 feet, the meter read 49 dB. Which is just above a bedroom at night. So the fan is not an issue. Any audio you have in your home theater system will be well above the fan in a dead quiet room.</p>

<p>The WD-65831 is a bit pricey at just under $3000 (its still cheaper than our first HDTVs that are still in use today. If you want the size but don't want to spend the Money, Mitsubishi has a WD-65731 for $1940 (Buy Now). It has many of the same features but a slightly less capable lamp system and light engine.</p>

<p><strong>Final Thoughts:</strong><br />
This is one of the best TVs we have seen and we look at allot of them. The colors are bright and vivid. Dark scene detail is impressive and HD looks real. But to get the most our of this TV you'll need to invest in a Calibration DVD or have it professionally calibrated. The only real complaints we have are that its a bit expensive and it needs the afore mentioned calibration support.</p>

<p><strong>Calibration Settings:</strong><ul><li>Contrast: 20</li><li>Brightness: 30</li><li>Color: 36</li><li>Tint: 34</li><li>Sharpness: 24</li><li>Color Temperature: Low</li><li>Picture Mode: Natural</li><li>Video Noise Reduction: Off</li><li>Sharp Edge: Off</li><li>Deep Field: On</li></ul></p>

<p><strong>Perfect Color</strong><ul><li>Magenta: 32</li><li>Red: 24</li><li>Yellow: 31</li><li>Green: 46</li><li>Cyan: 32</li><li>Blue: 32</ul></li></p>

<p><strong>Perfect Tint</strong><ul><li>Magenta: 47</li><li>Red: 29</li><li>Yellow: 29</li><li>Green: 55</li><li>Cyan: 46</li><li>Blue: 28</ul></li></p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 17, 2007 08:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 578
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
 			<h2>More on HDTV Displays</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HDTV Displays'
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
 				AND entry_id <> 578
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/04/mitsubishi_65_inch_1080p_dlp_wd-65831.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
