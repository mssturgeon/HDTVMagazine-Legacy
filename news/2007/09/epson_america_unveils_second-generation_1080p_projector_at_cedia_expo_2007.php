<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 708";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 708 AND placement_is_primary = 1";
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
	<meta name="keywords" content="epson america, america inc, pro cinema, home theater, epson powerlite, epson, Epson, america, lcd, new, America, LCD, technology, projector, Inc, inc, cinema, image, theater, Cinema, Pro, pro, home, powerlite, color" />
	<meta name="description" content="Epson, the number-one selling projector brand worldwide(1) continues to drive innovation in the home theater industry with a new high-end projector that delivers true 1080p (1,920 x 1,080 pixels) resolution and contrast ratios measuring up to 50,000:1. Incorporating the latest 3LCD optical engine -- the D7 chip set with C2Fine and new UltraBlack technology, this professional home theater projector is designed to deliver the industry's best image quality yet for projectors in the sub-$5,000 price category.

Today at CEDIA Expo 2007, Epson America unveiled its first home theater projector to take advantage of these engineering breakthroughs with the Epson PowerLite Pro Cinema 1080 UB. This new flagship model is built upon..." />
	<title>HDTV Magazine Bulletins - Epson America Unveils Second-Generation 1080p Projector at CEDIA Expo 2007</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Epson America Unveils Second-Generation 1080p Projector at CEDIA Expo 2007'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/09/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Epson America Unveils Second-Generation 1080p Projector at CEDIA Expo 2007</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  6, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/09/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/09/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/09/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007.php&amp;phase=2&amp;title=Epson%20America%20Unveils%20Second-Generation%201080p%20Projector%20at%20CEDIA%20Expo%202007&amp;bodytext=Epson%2C%20the%20number-one%20selling%20projector%20brand%20worldwide%281%29%20continues%20to%20drive%20innovation%20in%20the%20home%20theater%20industry%20with%20a%20new%20high-end%20projector%20that%20delivers%20true%201080p%20%281%2C920%20x%201%2C080%20pixels%29%20resolution%20and%20contrast%20ratios%20measuring%20up%20to%2050%2C000%3A1.%20Incorporating%20the%20latest%203LCD%20optical%20engine%20--%20the%20D7%20chip%20set%20with%20C2Fine%20and%20new%20UltraBlack%20technology%2C%20this%20professional%20home%20theater%20projector%20is%20designed%20to%20deliver%20the%20industry%27s%20best%20image%20quality%20yet%20for%20projectors%20in%20the%20sub-%245%2C000%20price%20category.%0A%0AToday%20at%20CEDIA%20Expo%202007%2C%20Epson%20America%20unveiled%20its%20first%20home%20theater%20projector%20to%20take%20advantage%20of%20these%20engineering%20breakthroughs%20with%20the%20Epson%20PowerLite%20Pro%20Cinema%201080%20UB.%20This%20new%20flagship%20model%20is%20built%20upon...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Epson America Unveils Second-Generation 1080p Projector at CEDIA Expo 2007 That Achieves 50,000:1 Contrast Ratio and All-Around Superior Image Quality</p>

<center><i>New Epson PowerLite(R) Pro Cinema 1080 UB Takes Advantage of a New 3LCD Chip Technology -- D7 with C2Fine(TM) -- to Achieve UltraBlack(TM) Contrast for Superior Output</i></center><br />
<br />

<p><B>DENVER, Sept. 7 /PRNewswire/</B> -- Epson, the number-one selling projector brand worldwide(1) continues to drive innovation in the home theater industry with a new high-end projector that delivers true 1080p (1,920 x 1,080 pixels) resolution and contrast ratios measuring up to 50,000:1. Incorporating the latest 3LCD optical engine -- the D7 chip set with C2Fine and new UltraBlack technology, this professional home theater projector is designed to deliver the industry's best image quality yet for projectors in the sub-$5,000 price category.</p>

<p>Today at CEDIA Expo 2007, Epson America unveiled its first home theater projector to take advantage of these engineering breakthroughs with the Epson PowerLite Pro Cinema 1080 UB. This new flagship model is built upon the new D7 chip set with C2Fine, a new inorganic substrate that uses 12-bit video processing and a new polarizing light filter. With this new technology, the Pro Cinema 1080 UB can deliver incredible blacks with excellent tone response in addition to elevating contrast ratios to much higher levels than previous models. Furthermore, by achieving brightness levels as high as 1,600 ANSI lumens, the new Pro Cinema 1080 UB is even well-suited for rooms where ambient light can't always be eliminated.</p>

<p>The Epson PowerLite Pro Cinema 1080 UB will be available in December through authorized CEDIA dealers; pricing will be announced at that time.</p>

<p>"Beyond offering the best 1080p image quality the market has seen in this price category, our engineers have also discovered numerous ways to incorporate other unique features and control options that enhance the installation, setup and calibration process," said Mike Isgrig, director of multimedia projection products, Epson America. "We're certain that most of today's professional home theater installers will appreciate these enhancements, but we also expect the incredible performance to satisfy the needs of today's most discriminating home theater customers."</p>

<p>The Epson PowerLite Pro Cinema 1080 UB is ISF(TM)-certified, ensuring it meets the home theater industry's highest standards for video fidelity and calibration. Other key features and benefits include:</p>

<p> -- Two HDMI 1.3 inputs provide connectivity for high definition video<br />
 formats including "future-ready" x.v.Color(TM) with extended color<br />
 gamut<br />
 -- Lens shift (96-degree vertical, and 47-degree horizontal) makes the<br />
 projector more adaptable to a wide range of room configurations that<br />
 may require special setup angles<br />
 -- Epson's exclusive OptiCinema(TM) lens system takes advantage of 14<br />
 lenses, including two aspherical lenses, to maintain superior picture<br />
 integrity across the entire image from corner to corner with zoom ratio<br />
 of 1:2.1<br />
 -- Six axis color adjustment, including new brightness control<br />
 -- Quieter operation; runs as low as 24 db<br />
 -- Epson E-TORL(TM) Lamp engineered to project light more efficiently to<br />
 maximize brightness, uniformity across the entire screen and lamp life<br />
 -- Stylish pearlescent black finish<br />
 -- Spare lamp and ceiling mount included<br />
 -- Three-year limited warranty</p>

<p><br />
<B>About 3LCD Technology</B></p>

<p>3LCD is the world's leading projection technology, delivering unbelievable color, amazing detail and road-tested reliability. Using an advanced 3-chip design, 3LCD offers full-time RGB for brilliant quality images without the possibility of color break-up. 3LCD is based on LCD technology, which is used by leading manufacturers worldwide for the ultimate viewing experience in a variety of products. For the latest list of companies using 3LCD technology in projectors and large screen TVs, visit the 3LCD website at http://www.3lcd.com/.</p>

<p><br />
<B>About Epson America Inc.</B></p>

<p>Epson offers an extensive array of award-winning image capture and image output products for the consumer, business, photography, and graphic arts markets. The company is also a leading supplier of value-added point-of-sale (POS) printers and transaction terminals for the retail market. Founded in 1975, Epson America Inc. is the U.S. affiliate of Japan-based Seiko Epson Corporation, a global manufacturer and supplier of high-quality technology products that meet customer demands for increased functionality, compactness, systems integration and energy efficiency. Epson America Inc. is headquartered in Long Beach, Calif.</p>

<p>Note: Epson and PowerLite are registered trademarks of Seiko Epson Corporation. Epson is a registered trademark and E-TORL is a trademark of Seiko Epson Corporation. C2Fine, UltraBlack, and OptiCinema are trademarks of Epson America, Inc. All other product and brand names are trademarks and/or registered trademarks of their respective companies. Epson disclaims any and all rights in these marks.</p>

<p>(1) Based upon Q2 2007 worldwide front projection market share estimates</p>

<p>from Pacific Media Associates.</p>

<p>Source: Epson America Inc.</p>

<p>CONTACT: John Jatinen of Epson America Inc., +1-562-290-5173,<br />
john_jatinen@ea.epson.com; or Sara Pfaff of Walt & Company,<br />
+1-408-369-7200, ext. 2980, spfaff@walt.com, for Epson America Inc.</p>

<p>Web site: http://www.epson.com/<br />
http://www.3lcd.com/</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  6, 2007 11:54 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 708
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
 				AND entry_id <> 708
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/09/epson_america_unveils_second-generation_1080p_projector_at_cedia_expo_2007.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
