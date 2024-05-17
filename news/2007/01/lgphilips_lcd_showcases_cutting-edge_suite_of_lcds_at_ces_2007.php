<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 498";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 498 AND placement_is_primary = 1";
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
	<meta name="keywords" content="philips lcd, tft lcd, lcd panels, lcd panel, looking statements, lcd, LCD, Philips, philips, inch, panels, panel, TFT, tft, full, statements, looking, information, power, technology, lcds, display, including, LCDs, ces" />
	<meta name="description" content="LG.Philips LCD Co., Ltd. (NYSE:LPL)(KRX:034220), a leading innovator of thin-film transistor liquid crystal display (TFT-LCD) technology, announced today that it will showcase its cutting-edge suite of TFT-LCDs for HDTVs, desktop monitors, notebook PCs, digital information displays and other applications during CES 2007 in Las Vegas, Nevada, to be held from January 8-11, 2007.

Products showcased will include a full line-up of full high definition..." />
	<title>HDTV Magazine Bulletins - LG.Philips LCD Showcases Cutting-Edge Suite of LCDs at CES 2007</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('LG.Philips LCD Showcases Cutting-Edge Suite of LCDs at CES 2007'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/01/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">LG.Philips LCD Showcases Cutting-Edge Suite of LCDs at CES 2007</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/01/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/01/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/01/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/01/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007.php&amp;phase=2&amp;title=LG.Philips%20LCD%20Showcases%20Cutting-Edge%20Suite%20of%20LCDs%20at%20CES%202007&amp;bodytext=LG.Philips%20LCD%20Co.%2C%20Ltd.%20%28NYSE%3ALPL%29%28KRX%3A034220%29%2C%20a%20leading%20innovator%20of%20thin-film%20transistor%20liquid%20crystal%20display%20%28TFT-LCD%29%20technology%2C%20announced%20today%20that%20it%20will%20showcase%20its%20cutting-edge%20suite%20of%20TFT-LCDs%20for%20HDTVs%2C%20desktop%20monitors%2C%20notebook%20PCs%2C%20digital%20information%20displays%20and%20other%20applications%20during%20CES%202007%20in%20Las%20Vegas%2C%20Nevada%2C%20to%20be%20held%20from%20January%208-11%2C%202007.%0A%0AProducts%20showcased%20will%20include%20a%20full%20line-up%20of%20full%20high%20definition...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">LG.Philips LCD Showcases Cutting-Edge Suite of LCDs at CES 2007</p>

<p>2007 International CES<br />
Booth: N247, N 249</p>

<p><b>SEOUL, South Korea--(BUSINESS WIRE)</b>--LG.Philips LCD Co., Ltd. (NYSE:LPL)(KRX:034220), a leading innovator of thin-film transistor liquid crystal display (TFT-LCD) technology, announced today that it will showcase its cutting-edge suite of TFT-LCDs for HDTVs, desktop monitors, notebook PCs, digital information displays and other applications during CES 2007 in Las Vegas, Nevada, to be held from January 8-11, 2007.</p>

<p>Products showcased will include a full line-up of full high definition (Full HD) (1920 x 1080, two megapixels) TFT-LCD panels for HDTVs, as well as a fleet of small and medium display solutions featuring the latest next-generation technologies such as low-temperature polysilicon (LTPS). Also to be demonstrated are RoHS-compliant LCD panels for information displays and LG.Philips LCD's flagship 100-inch TFT-LCD panel, the largest in the world.</p>

<p>LG.Philips LCD's booth will also feature a separate Low-Power Technology Zone demonstrating a 42-inch LCD TV panel that uses 30 percent less power than conventional models by featuring an advanced power controlling technology. It will also showcase a 26-inch LCD TV panel that uses 35 percent less power but has increased brightness because of the addition of a white subpixel to each pixel in this section. In addition, there will be a 2-inch panel for mobile phones that consumes 30 percent less power through constant analysis of onscreen data.</p>

<p>Mr. Sang Deog Yeo, executive vice president and head of the TV Business Unit at LG.Philips LCD, said, "As digital broadcasting becomes more widespread and HDTV prices become more attractive, the demand for our products will continue to rise. We believe our full line-up of full HD TFT-LCD panels will enable us to better respond to the needs of our customers and satisfy the increasing consumer demand."</p>

<p>According to DisplaySearch, the full HD TFT-LCD panel market is expected to grow 237 percent, from 1.6 million units in 2006 to 5.4 million units in 2007.</p>

<p>A sampling of what LG.Philips LCD will exhibit at CES 2007 includes:</p>

<p><br />
<h2>Full HD Panels for HDTVs and Information Displays</h2><br />
LG.Philips LCD will demonstrate its full line-up of 37-, 42-, 47- and 52-inch full high definition (1920 x 1080, two mega pixels) widescreen panels for HDTVs. The panels feature a dynamic contrast ratio of 5,000:1, 180-degree viewing angle, brightness exceeding 500 cd/m(2), and boast superior response time and high color gamut.</p>

<p>Panels designed for the emerging flat-panel information display market will also be on display, including 42-, 47- and 55-inch sizes. All of these panels are RoHS-compliant.</p>

<p><br />
<h2>Small & Medium LCDs with Cutting-Edge Technology</h2><br />
LG.Philips LCD will introduce 7- and 8-inch LCDs for car navigation as well as other medium-size displays in sizes including 6.4-, 8.4- and 10.4-inches. The company will also demonstrate its category leading 2.8-inch LTPS LCD panel with In-Plane Switching (IPS). Additionally, the company's 2.6- and 3.5-inch VGA LTPS LCD panels and 2.2-, 2.4- and 3-inch QVGA organic light-emitting diode (OLED) panels will be on display. These OLED panels feature a contrast ratio of 10,000:1 and response time of 0.01m/s.</p>

<p><br />
<h2>Premium Desktop Monitor and Notebook PC Panels</h2><br />
LG.Philips LCD will show its fleet of TFT-LCD panels for desktop monitors, including the world's largest 30-inch wide QXGA+ (2560 x 1600) LCD panel that boasts superior color gamut of 117 percent. The latest in TFT-LCD technology for notebook PCs will also be featured, including the world's largest, a 20.1-inch wide SXGA+ (1680 x 1050) LCD panel.</p>

<p><br />
<h2>Future LCD Technology</h2><br />
LG.Philips LCD's prototype demonstrations feature new technologies for LCDs, including:</p>

<p>- 15-inch monitor LCD panel which can control viewing-angle (178 degrees, 80 degrees) with a single cell</p>

<p>- 32-inch HDTV LCD TV panel which lowers power consumption to 65-watt by featuring an external electrode fluorescent lamp(EEFL)</p>

<p>- 42-inch HDTV LCD panel with a vastly improved motion picture response time (MPRT)</p>

<p>Media interested in learning more about LG.Philips LCD's product portfolio first hand are encouraged to visit the company's booth at CES 2007 in meeting room N247/N249 located within the North Hall of the Las Vegas Convention Center (LVCC).</p>

<p><br />
<h2>About LG.Philips LCD</h2><br />
LG.Philips LCD Co., Ltd (NYSE:LPL)(KRX:034220) is a leading manufacturer and supplier of thin-film transistor liquid crystal display (TFT-LCD) panels. The Company manufactures TFT-LCD panels in a wide range of sizes and specifications for use in TVs, monitors, notebook PCs, and various applications. LG.Philips LCD currently operates seven fabrication facilities and four back-end assembly facilities in Korea, China and Poland. In addition, LG.Philips LCD has sales and representative offices in ten countries and has approximately 21,000 employees globally. Please visit http://www.lgphilips-lcd.com for more information.</p>

<p><br />
<h2>Forward-Looking Statement Disclaimer</h2><br />
This press release may contain forward-looking statements. Statements that are not historical facts, including statements about our beliefs and expectations, are forward-looking statements. These statements are based on current plans, estimates and projections, and therefore you should not place undue reliance on them. Forward-looking statements speak only as of the date they are made, and we undertake no obligation to update publicly any of them in light of new information or future events. Forward-looking statements involve inherent risks and uncertainties. We caution you that a number of important factors could cause actual results to differ materially from those contained in any forward-looking statement. Additional information as to factors that may cause actual results to differ materially from our forward-looking statements can be found in our filings with the United States Securities and Exchange Commission.</p>

<p><br />
<b>Contacts</b><br />
LG.Philips LCD<br />
Bang-Soo Lee, +822-3777-1020 (VP, Public Affairs & PR)<br />
bsleeb@lgphilips-lcd.com<br />
Sue Kim, +822-3777-0970 (Senior Manager, Corporate PR)<br />
sue.kim@lgphilips-lcd.com</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2007 08:26 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 498
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
 				AND entry_id <> 498
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/01/lgphilips_lcd_showcases_cutting-edge_suite_of_lcds_at_ces_2007.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
