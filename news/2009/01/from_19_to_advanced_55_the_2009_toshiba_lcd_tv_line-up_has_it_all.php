<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1620";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1620 AND placement_is_primary = 1";
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
	<meta name="keywords" content="inch inch, screen sizes, inch screen, toshiba america, sizes measured, series, toshiba, Toshiba, REG, inch, reg, new, Series, hdmi, design, HDMI, available, models, sizes, screen, products, line, digital, Available, high" />
	<meta name="description" content="Toshiba America Consumer Products, L.L.C. (&quot;Toshiba&quot;), a market leader in LCD TVs, today announced its full 2009 line-up of LCD televisions offering striking new cosmetics, unique technologies, more advanced features and additional screen sizes. From 19-inch to new 55-inch Cinema Series(R) models, Toshiba's line-up offers..." />
	<title>HDTV Magazine Bulletins - From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all.php&amp;phase=2&amp;title=From%2019%20to%20Advanced%2055%22%2C%20the%202009%20Toshiba%20LCD%20TV%20Line-Up%20Has%20It%20All&amp;bodytext=Toshiba%20America%20Consumer%20Products%2C%20L.L.C.%20%28%22Toshiba%22%29%2C%20a%20market%20leader%20in%20LCD%20TVs%2C%20today%20announced%20its%20full%202009%20line-up%20of%20LCD%20televisions%20offering%20striking%20new%20cosmetics%2C%20unique%20technologies%2C%20more%20advanced%20features%20and%20additional%20screen%20sizes.%20From%2019-inch%20to%20new%2055-inch%20Cinema%20Series%28R%29%20models%2C%20Toshiba%27s%20line-up%20offers...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">From 19 to Advanced 55", the 2009 Toshiba LCD TV Line-Up Has It All</p>

<center><i>High Quality, Unique Technology, Next-gen Connectivity, Industry Leading Design, and Logical Line Steps Combine to Mark a New Era for Toshiba LCD TV</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 7 /PRNewswire/</B> -- CES -- Toshiba America Consumer Products, L.L.C. ("Toshiba"), a market leader in LCD TVs, today announced its full 2009 line-up of LCD televisions offering striking new cosmetics, unique technologies, more advanced features and additional screen sizes. From 19-inch to new 55-inch Cinema Series(R) models, Toshiba's line-up offers a TV to fit every room and lifestyle at every price point.</p>

<p>"The new REGZA is 'more than you expected, and everything you need,'" said Scott Ramirez, VP, Marketing. "The level of technology we have packed in at every price point is certainly more than you would expect, and with all of our new series Toshiba definitely has everything you need."</p>

<p><br />
<h2>THE 2009 TOSHIBA LCD TV SERIES - more Series, more features, more opportunities</h2></p>

<p><B>AV600 Series - 720p HD - Small Sizes, Big Features</B></p>

<p>Toshiba's AV600 series packs a powerful punch in a small package. These sets are offered in 19-inch and 22-inch screen sizes (measured diagonally) in a new high-gloss black cosmetic design, and are a great choice for consumers looking to replace old analog sets to prepare for the upcoming digital TV transition.</p>

<p>AV600 models feature DynaLight(TM) dynamic backlight control for deeper black levels and two HDMI(TM) digital inputs for simple high quality connection to cable/satellite boxes, DVD players and more. These models also include a PC Input for use as a PC monitor or gaming TV, and unlike other smaller TVs they also feature Gaming Mode to shorten the signal to screen time for faster game controller response. Plus, all Toshiba 2009 TV models will meet ENERGY STAR(R) 3.0 certifications.</p>

<p><br />
<B>AV502 - Mid-Size 720p HD</B></p>

<p>Toshiba's AV502 series mid-size 720p HD models just started shipping in September and will continue throughout 2009. Available in 26-inch, 32-inch and 37-inch screen sizes (measured diagonally), these models come fully equipped with DynaLight, Gaming Mode, 2 HDMI Digital Inputs and a PC Input. Cosmetically, these models feature their own attractive high-gloss black design.</p>

<p><br />
<B>RV525 - 1080p Full HD</B></p>

<p>Stepping to 1080p Full HD is the Toshiba RV525 Series. Also shipping now and continuing throughout 2009, these models have 3 HDMI digital inputs with REGZA-Link(R) (HDMI CEC) and a High-Res PC Input. Available in 40-inch and 46-inch screen sizes (measured diagonally), this series also adds QSound(R) for enhanced audio quality.</p>

<p><br />
<B>REGZA XV645 Series - REGZA Engine, ClearFrame(TM) 120Hz, and Bottom Deep Lagoon(TM) Design</B></p>

<p>Toshiba's REGZA line is powered by Toshiba's PixelPure(R) 5G 14 Bit internal digital video processing, the heart of the REGZA engine. All REGZA models also include Resolution+ super resolution technology, the soul of the REGZA engine, to upconvert and enhance 480i/480p and even 720p signals to create increased detail so everything will feel like HD.</p>

<p>The XV645 series is available in 40-inch, 46-inch and 52-inch screen sizes (measured diagonally), and includes ClearFrame 120Hz technology for clearer fast motion video, as well as AutoView(TM), which automatically sets picture settings based on the incoming video signal and ambient room light. This series includes three HDMI digital inputs with enhanced REGZA-LINK (HDMI-CEC) and a high-resolution PC input. Cosmetically, this series features Toshiba's new Bottom Deep Lagoon design with invisible speakers. Inspired by nature, this design provides a three dimensional feeling to a clean, flat design.</p>

<p><br />
<B>REGZA ZV650 Series - An All-New Level with ClearScan 240(TM), CrystalCoat(TM), Dolby(R) Volume, and Next Gen Connectivity</B></p>

<p>The REGZA ZV650 series is available in 42-inch, 47-inch and new 55-inch screen sizes (measured respectively at 42, 47 and 54.6-inches diagonally) and adds ClearScan 240, which combines a 120Hz frame rate with new Backlight Scanning Technology to create a 240Hz effect for an all new level of picture clarity with fast motion video. For improved sound, the ZV650 series also adds Dolby Volume, which eliminates annoying, inconsistent volume levels from commercials or when changing channels. Connectivity is also taken to an all new level with four HDMI digital inputs that have enhanced REGZA-Link (HDMI-CEC), and InstaPort(TM) for faster HDMI switching. To allow playback of downloaded content, this line also adds a USB Port allowing consumers to watch their JPEG pictures, MPEG-1 & 2 video or even DivX(R) video and listen to MP3 audio. There is also an SD Card Slot that allows easy viewing of JPEG digital pictures by simply taking the memory card from the camera and placing it in the TV.</p>

<p>Cosmetically, this series features the Full Deep Lagoon Design with invisible speakers.</p>

<p><br />
<B>REGZA SV670 Series - FocaLight(TM) LED Backlight with Local Dimming and Stunning Infinity Flush Front(TM) Design</B></p>

<p>Available in 46-inch and 55-inch screen sizes (measured respectively at 46 and 54.6-inches diagonally), this state-of-the-art series adds FocaLight LED Backlight with Local Dimming. Unlike simple edge LED, FocaLight offers a full LED matrix for enhanced brightness uniformity. Even more important is the local dimming, which creates significantly higher dynamic contrast, as blacks are blacker without reducing the peak white brightness.</p>

<p>The ultimate in cosmetic design, this series features Toshiba's Deep Lagoon process behind the new Infinity Flush Front. Inspired by an infinity pool, this design uses a solid sheet of anti-reflective glass across the entire front of the TV. The stunning appearance of the SV670 will set a new standard in LCD TV design.</p>

<p><br />
<B>An Internet TV</B></p>

<p>Toshiba plans to introduce an additional series later in 2009 that will incorporate the new TV Widgets, enabled by the Widget Channel framework, which allows consumers direct and easy access to sources of personalized Internet content via their remote control. A rich array of content is available, such as local weather, top news stories, favorite sports scores, stock quotes, pictures, videos and more, and they are all just a click away. In addition, DLNA and Microsoft's Extender for Windows Media Center capabilities will also be added to allow remote control of a compatible PC. With DLNA and Microsoft's Extender for Windows Media Center, the user can access all of the multi-media content stored on their PC's hard disc drive.</p>

<p><br />
<B>Expected Models and Availability:</B></p>

<p>  AV600 Series<br />
  19AV600U (March 2009)<br />
  22AV600U (March 2009)</p>

<p>  AV502 Series<br />
  26AV502U (Available Now)<br />
  32AV502U (Available Now)<br />
  37AV502U (Available Now)</p>

<p>  RV525 Series<br />
  40RV525U (Available Now)<br />
  46RV525U (Available Now)</p>

<p>  REGZA XV645 Series<br />
  40XV645U (May 2009)<br />
  46XV645U (May 2009)<br />
  52XV645U (May 2009)</p>

<p>  REGZA ZV650 Series<br />
  42ZV650U (April 2009)<br />
  47ZV650U (April 2009)<br />
  55ZV650U (April 2009)</p>

<p>  REGZA SV670 Series<br />
  46SV670U (May 2009)<br />
  55SV670U (May 2009)</p>

<p>  Additional REGZA Series to be announced</p>

<p><br />
<B>About Toshiba America Consumer Products, L.L.C.</B></p>

<p>Toshiba America Consumer Products, L.L.C. is owned by Toshiba America, Inc., a subsidiary of Toshiba Corporation, a world leader in high technology products with subsidiaries worldwide. Toshiba is a pioneer in DVD technology and a leading manufacturer of a full line of home entertainment products, including flat panel TVs, combination products and portable devices. Toshiba America Consumer Products, L.L.C. is headquartered in Wayne, New Jersey. For additional information please visit www.tacp.toshiba.com.</p>

<p>DivX, DivX Certified, and associated logos are trademarks of DivX, Inc. and are used under license.</p>

<p>Dolby is a registered trademark of Dolby Laboratories.<br />
ENERGY STAR is a registered mark owned by the U.S. Government.</p>

<p>HDMI, the HDMI logo and High-Definition Multimedia Interface are trademarks or registered trademarks of HDMI Licensing, L.L.C.</p>

<p>QSound is a registered trademark of QSound Labs, Inc.</p>

<p>All others are trademarks or registered trademarks of their respective companies.</p>

<p>Source: Toshiba America Consumer Products, L.L.C. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2009 11:06 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1620
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
 				AND entry_id <> 1620
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/from_19_to_advanced_55_the_2009_toshiba_lcd_tv_line-up_has_it_all.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
