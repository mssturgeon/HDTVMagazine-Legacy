<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1529";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1529 AND placement_is_primary = 1";
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
	<meta name="keywords" content="inch inch, toshiba america, cinema series, double skin, consumer products, toshiba, Toshiba, series, new, technology, Series, REG, inch, SRT, lcd, reg, LCD, srt, line, hdmi, September, autoview, AutoView, products, september" />
	<meta name="description" content="Toshiba America Consumer Products, L.L.C. (&quot;Toshiba&quot;) today unveiled SRT(TM), Super Resolution Technology, a new technology that changes the way standard definition content is viewed. SRT upconverts standard definition content to feel like HD. Toshiba also launched 11 new LCD TVs in four new series to its broad range of Toshiba, REGZA(R) and Cinema Series(R) brands.

Toshiba is..." />
	<title>HDTV Magazine Bulletins - Toshiba Announces Fall LCD TV Lineup Featuring First-of-its-Kind Upconverting Technology For TVs</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Toshiba Announces Fall LCD TV Lineup Featuring First-of-its-Kind Upconverting Technology For TVs'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Toshiba Announces Fall LCD TV Lineup Featuring First-of-its-Kind Upconverting Technology For TVs</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  4, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs.php&amp;phase=2&amp;title=Toshiba%20Announces%20Fall%20LCD%20TV%20Lineup%20Featuring%20First-of-its-Kind%20Upconverting%20Technology%20For%20TVs&amp;bodytext=Toshiba%20America%20Consumer%20Products%2C%20L.L.C.%20%28%22Toshiba%22%29%20today%20unveiled%20SRT%28TM%29%2C%20Super%20Resolution%20Technology%2C%20a%20new%20technology%20that%20changes%20the%20way%20standard%20definition%20content%20is%20viewed.%20SRT%20upconverts%20standard%20definition%20content%20to%20feel%20like%20HD.%20Toshiba%20also%20launched%2011%20new%20LCD%20TVs%20in%20four%20new%20series%20to%20its%20broad%20range%20of%20Toshiba%2C%20REGZA%28R%29%20and%20Cinema%20Series%28R%29%20brands.%0A%0AToshiba%20is...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Toshiba Announces Fall LCD TV Lineup Featuring First-of-its-Kind Upconverting Technology For TVs</p>

<center><i>Super Resolution Technology (SRT) Delivers New Level of Picture Quality Making SD Content Feel Like Full HD</i></center><br />
<br />

<p><B>CEDIA, DENVER, Colo., Sept. 4 /PRNewswire/ </B>-- Toshiba America Consumer Products, L.L.C. ("Toshiba") today unveiled SRT(TM), Super Resolution Technology, a new technology that changes the way standard definition content is viewed. SRT upconverts standard definition content to feel like HD. Toshiba also launched 11 new LCD TVs in four new series to its broad range of Toshiba, REGZA(R) and Cinema Series(R) brands.</p>

<p>Toshiba is solidifying its position as an innovative leader in the LCD TV market. SRT brings clean, sharp edges and bright, vibrant colors to standard definition content making DVD collections and non-HD TV channels look better than ever before. Also new is AutoView(TM), a feature that automatically adjusts picture settings based on the ambient room lighting and the type of video content being displayed, which can help consumers save energy while improving the viewing experience.</p>

<p>"The TV industry is now hyper-dynamic, with faster change in both technology and price points," said Scott Ramirez, VP, Marketing. "Toshiba is now setting the new pace of change by announcing a second new TV line-up for fall 2008. We are introducing new leading technologies, like SRT, and also strengthening our core models for the key selling season. Toshiba's growth in LCD TV is already outpacing the industry, and this new line-up will help solidify Toshiba's position as a true leader."</p>

<p><br />
<B>Toshiba Makes Standard Definition Feel Like HD</B></p>

<p>Making its debut in Toshiba's REGZA LCD TVs is Toshiba's SRT, a new technology that overcomes an industry-wide issue, in which consumers are often watching standard video signals on their HDTVs. Using a unique proprietary processing system, SRT improves image sharpness, brightness and color, enhancing standard movies, TV shows, camcorder movies and most video games to near HD quality. This new technology is available in Toshiba's REGZA RV535 and XV545 series.</p>

<p><br />
<B>Toshiba Takes the Guesswork out of Home Theater Viewing</B></p>

<p>Through the use of a built-in light sensor and intelligent algorithms, AutoView ensures increased image quality at all times, no matter the surroundings. Consumers don't have to worry about the glare from afternoon sunlight or eye strain when watching TV in a darkened room. Not only does AutoView make life simpler and the picture better, but it can potentially lower power consumption, providing energy savings for the home. Like SRT, AutoView is available in the RV535 and XV545 REGZA series.</p>

<p><br />
<B>THE FALL 2008 TOSHIBA LCD TV SERIES: Cinema Series Returns!</B></p>

<p>XV545 Series -- REGZA Cinema Series with SRT, AutoView, ClearFrame and Double Skin Cabinetry</p>

<p>Toshiba is relaunching its exclusive and highly-regarded Cinema Series LCD TVs with the REGZA XV545 series. The top-of-the-line Cinema Series XV545 line offers advanced technology and picture quality and is available in 42.0-inch, 46.0-inch and 52.0-inch sizes (diagonal). This lineup includes Toshiba's new SRT upconversion technology, the AutoView feature for enhanced image quality at all times, no matter the surroundings, as well as Toshiba's new Double Skin Cabinetry. With double skin, the cabinet's outer skin is translucent, allowing you to see through to a second interior skin with texture behind it -- creating a feeling of dimension and a rich, luxurious look.</p>

<p>For fast action movies and sports, the XV545 line features Toshiba's ClearFrame(TM) technology, which virtually eliminates motion blur. The 120Hz system doubles the frame rate of an LCD TV from 60 frames per second to 120 frames per second, creating tire-spinning action sequences and game-winning sports moments that are more crisp and clear. All REGZA models with ClearFrame 120 Hz technology include Film Stabilization mode for a picture with smoother motion, and for those customers preferring a more film-like experience, Toshiba has also implemented a 5:5 Pull-Down process. This series also features 14-Bit PixelPure(R) 4G Internal Digital Video Processing with 10-Bit LCD panels for 16,384 levels of gradation and Toshiba's ColorBurst(TM) wide color gamut for rich lifelike color. In addition, the XV545 series includes four HDMI inputs with REGZA LINK (HDMI(TM)-CEC) and Toshiba's SoundStrip(R) speaker technology, which allows for home theater sound in a small strip beneath the screen.</p>

<p>To keep the avid gamer one step ahead, the line also offers Gaming Mode, which enhances the gaming experience by reducing game controller delay. When activated, Gaming Mode allows the video signal to bypass select picture circuitry to shorten the overall signal to screen response time. What this means is that the gamer has improved reaction time and a better overall experience.</p>

<p>Like all of Toshiba's new LCD TV lines, the XV545 features a CineSpeed(TM) panel for faster response times, DynaLight(TM) control for deeper black levels and is ENERGY STAR(R) compliant.</p>

<p>RV535 Series -- REGZA 1080p Full HD with SRT, AutoView and Double Skin Cabinetry</p>

<p>The REGZA RV535 series, available in 42.0-inch, 46.0-inch and 52.0-inch (diagonal) screen sizes, features Toshiba's PixelPure 4G 14-bit internal digital video processor, with 4,096 levels of gradation, as well as four HDMI(TM) inputs with REGZA LINK (HDMI-CEC) and Toshiba's SoundStrip speaker technology. Like the XV545 line, the RV535 lineup includes Toshiba's new SRT upconversion technology and AutoView, as well as Toshiba's new Double Skin Cabinetry.</p>

<p><br />
<B>RV525 Series -- 1080p Full HD</B></p>

<p>Available in 40.0-inch and 46.0-inch (diagonal) screen sizes, Toshiba's RV525 series provides 1080p Full HD resolution, offering consumers impressive high-definition quality at attractive price points in beautiful high-gloss cabinetry. The RV525 series features DynaLight Dynamic Backlight control for deep blacks, Native Mode(TM) to restore picture size, Gaming Mode for fast controller to screen response time and a high-res PC input.</p>

<p><br />
<B>AV502 Series -- 720p HD</B></p>

<p>Replacing Toshiba's AV500 series in the 26.0-inch, 31.5-inch and 37.0-inch (diagonal) sizes, Toshiba's new entry-level 720p LCD TV series offers high-gloss black cabinetry and a thinner bezel than its predecessor, plus two HDMI inputs and a PC input, creating a very feature rich entry-level lineup. Toshiba is also adding a 21.6-inch (diagonal) screen size into the existing AV500 series. Wrapped in a high-gloss cabinet and standing less than 18-inches high to fit beneath standard kitchen cabinets, this set joins Toshiba's 19.0-inch (diagonal) AV500 models.</p>

<p>In addition to the four new series Toshiba is introducing, Toshiba will continue to offer select, popular models from its current line of LCD TVs. For more information or to find out which TV is right for you, please visit http://www.regza.com/.</p>

<p><br />
<B>Expected Availability & Pricing of New Fall Lineup:</B></p>

<p>  REGZA Cinema Series XV545 Series<br />
  42XV545U ($1799.99 September)<br />
  46XV545U ($2299.99 September)<br />
  52XV545U ($2799.99 September)</p>

<p>  REGZA RV535 Series<br />
  42RV535U ($1399.99 September)<br />
  46RV535U ($1799.99 September)<br />
  52RV535U ($2299.99 September)</p>

<p>  RV525 Series<br />
  40RV525U ($1099.99 September)<br />
  46RV525U ($1599.99 October)</p>

<p>  AV502 Series<br />
  26AV502U ($649.99 August)<br />
  32AV502U ($749.99 August)<br />
  37AV502U ($899.99 August)</p>

<p>  AV500 Series<br />
  22AV500U ($499.99 September)</p>

<p><br />
<B>About Toshiba America Consumer Products, L.L.C.</B></p>

<p>Toshiba America Consumer Products, L.L.C. is owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation, a world leader in high technology products with subsidiaries worldwide. Toshiba is a pioneer in DVD and DVD Recorder technology and a leading manufacturer of a full line of home entertainment products, including flat panel TV, rear projection and direct view televisions, combination products and portable devices. Toshiba America Consumer Products, L.L.C. is headquartered in Wayne, New Jersey. For additional information please visit http://www.tacp.toshiba.com/.</p>

<p>Important Notes:</p>

<p>Effect of SRT enhancements may vary depending upon the input signal and content quality.</p>

<p>ENERGY STAR is a registered mark owned by the U.S. Government.</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing, L.L.C.</p>

<p>All others are trademarks or registered trademarks of their respective companies.</p>

<p>Source: Toshiba America Consumer Products, L.L.C. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  4, 2008 01:52 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1529
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
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
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
 				AND entry_id <> 1529
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/toshiba_announces_fall_lcd_tv_lineup_featuring_first-of-its-kind_upconverting_technology_for_tvs.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
