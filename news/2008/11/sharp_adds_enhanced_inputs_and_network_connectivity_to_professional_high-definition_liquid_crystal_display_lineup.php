<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1546";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1546 AND placement_is_primary = 1";
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
	<meta name="keywords" content="lcd monitor, professional lcd, high definition, added resellers, sharp authorized, Sharp, sharp, monitor, professional, high, LCD, display, lcd, Professional, digital, image, mode, information, new, landscape, added, both, corporation, Corporation, Display" />
	<meta name="description" content="Sharp is expanding its comprehensive line-up of professional LCD displays with the announcement of two Monitors that represent upgrades to two successful &quot;PN&quot; series monitors. The new PN-S525, designed for extended use in both landscape and portrait mode, replaces its predecessor - model PN-525U, while the new PN-S655, designed for extended use in landscape mode, replaces its predecessor - model PN-G655U. These new models offer high image quality and performance, adding a new LCD component panel for improved resistance to image retention, as well as additional inputs including HDMI and RJ-45 LAN for advanced connectivity and networking capability. Also..." />
	<title>HDTV Magazine Bulletins - Sharp Adds Enhanced Inputs and Network Connectivity to Professional High-Definition Liquid Crystal Display Lineup</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sharp Adds Enhanced Inputs and Network Connectivity to Professional High-Definition Liquid Crystal Display Lineup'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/11/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sharp Adds Enhanced Inputs and Network Connectivity to Professional High-Definition Liquid Crystal Display Lineup</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>November 10, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/11/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/11/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/11/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/11/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup.php&amp;phase=2&amp;title=Sharp%20Adds%20Enhanced%20Inputs%20and%20Network%20Connectivity%20to%20Professional%20High-Definition%20Liquid%20Crystal%20Display%20Lineup&amp;bodytext=Sharp%20is%20expanding%20its%20comprehensive%20line-up%20of%20professional%20LCD%20displays%20with%20the%20announcement%20of%20two%20Monitors%20that%20represent%20upgrades%20to%20two%20successful%20%22PN%22%20series%20monitors.%20The%20new%20PN-S525%2C%20designed%20for%20extended%20use%20in%20both%20landscape%20and%20portrait%20mode%2C%20replaces%20its%20predecessor%20-%20model%20PN-525U%2C%20while%20the%20new%20PN-S655%2C%20designed%20for%20extended%20use%20in%20landscape%20mode%2C%20replaces%20its%20predecessor%20-%20model%20PN-G655U.%20These%20new%20models%20offer%20high%20image%20quality%20and%20performance%2C%20adding%20a%20new%20LCD%20component%20panel%20for%20improved%20resistance%20to%20image%20retention%2C%20as%20well%20as%20additional%20inputs%20including%20HDMI%20and%20RJ-45%20LAN%20for%20advanced%20connectivity%20and%20networking%20capability.%20Also...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sharp Adds Enhanced Inputs and Network Connectivity to Professional High-Definition Liquid Crystal Display Lineup</p>

<p><B>MAHWAH, N.J.--(BUSINESS WIRE)</B>--Sharp is expanding its comprehensive line-up of professional LCD displays with the announcement of two Monitors that represent upgrades to two successful "PN" series monitors. The new PN-S525, designed for extended use in both landscape and portrait mode, replaces its predecessor - model PN-525U, while the new PN-S655, designed for extended use in landscape mode, replaces its predecessor - model PN-G655U. These new models offer high image quality and performance, adding a new LCD component panel for improved resistance to image retention, as well as additional inputs including HDMI and RJ-45 LAN for advanced connectivity and networking capability. Also joining the professional LCD monitor lineup next month are the PN-S525P and PN-S655P, each with an integrated protective acrylic overlay for high traffic areas.</p>

<p>Sharp's diverse Professional LCD monitor line-up is ideal for use in many different market settings including medical imaging, digital signage, CAD/simulation, conference facilities, retail and hospitality, rental/staging, universities and many more.</p>

<p>"With a constantly evolving high-end, large-screen LCD display suite, Sharp continues to provide customers with the ideal monitor for any commercial setting," said Dan Wynne, senior director, Marketing, Professional Display Division, Sharp Imaging and Information Company of America. "With the added connectivity options, these new LCD monitors can receive the most accurate 1080p signals for precise image reproduction, and the added LAN connection enables quick remote diagnostics -- minimizing downtime, expediting maintenance, and providing a more reliable display for a wide range of commercial applications."</p>

<p><br />
<B>PN-S525 Professional LCD Monitor</B></p>

<p>The PN-S525U 52-inch class (52 1/32" Diagonal) monitor is designed for the most sophisticated digital signage and display applications in both landscape and portrait modes, with virtually no concern for image retention. This full two-megapixel high-definition (HD) monitor utilizes Sharp's Advanced Super View (AVS) / Black TFT panel for unparalleled image quality with a high contrast ratio (1800:1) and a pixel response time of 6 ms, enabling professional presentations of information and images. An ultra-high aperture LCD achieves high brightness and wide 176-degree viewing angles for a crisp picture that can be seen from virtually anywhere in a room.</p>

<p>Expanding on the former PN-525U's extensive array of inputs including DVI-D, Component Video and RS-232C for system control, the new PN-S525 adds an HDMI input for digital 1080p content as well as an RJ-45 LAN input, allowing the monitor to be controlled, monitored and have diagnostics performed from a central location.</p>

<p>With a streamlined black bezel, rugged rear metal enclosure and a removable front bezel open frame design, the monitor is built for superior performance and can be customized for virtually any digital signage or information display setting. A unique fanless design and optimized internal cooling and backlight system ensure reliable operation and backlight life, even in 24x7 operation.</p>

<p><br />
<B>PN-S655 Professional LCD Monitor</B></p>

<p>The PN-S655 65-inch class (64 17/32" Diagonal) LCD monitor is designed for landscape operation, offering superior performance for use in a wide range of commercial settings. A full 1920 x 1080 two-megapixel high-definition (HD) resolution and an impressive 2000:1 contrast ratio enable the monitor to offer accurate and precise image reproduction with nearly no image retention, and a bright picture in virtually any lighting condition.</p>

<p>With a rugged design and components, the monitor offers superior reliability for extended use applications. To show multiple types of content sources in high definition, the monitors utilize DFE (Dual-Fine Engine) technology, which provides superior display of both digital and analog sources so the customer can display computer and video content flawlessly. The monitor also has a full complement of input and output connectors to show all types of content, from DVD to varying PC resolutions up through 1920 x 1080 native mode from both analog and digital (DVI-D) sources for all types of presentations. The PN-S655 also adds an HMDI input for digital 1080p sources and an RJ-45 LAN input for remote diagnostics and control.</p>

<p>Sharp's proprietary ASV / Black TFT panel provides stellar picture quality for fast-moving video through its fast pixel response time of less than 6 ms, as well as high brightness of 420 cd/m2 and wide 176 degree viewing angles while maintaining exceptionally low power consumption.</p>

<p>The PN-S525 and PN-S525P are supported with a three-year on-site limited warranty covering parts, labor and backlight in both landscape and portrait mode. The PN-S655 and PN-S655P are supported with a three-year on-site limited warranty covering parts, labor and backlight in landscape mode only. [For professional LCD monitor applications requiring a 65-inch class (64 17/32" Diagonal) screen size in portrait mode orientation, Sharp also offers the PN-655RU and PN-655RUP (with integrated protective overlay). These models are supported with a three-year on-site limited warranty covering parts, labor and backlight in portrait mode only.]</p>

<p><br />
<B>Pricing and Availability</B></p>

<p>    * The PN-S525 is available now through Sharp authorized dealers and value-added resellers for a Manufacturer's Suggested List Price (MSLP) of $4,550<br />
    * The PN-S525P will be available in December through Sharp authorized dealers and value-added resellers for an MSLP of $6,459<br />
    * The PN-S655 is available now through Sharp authorized dealers and value-added resellers for an MSLP of $10,795<br />
    * The PN-S655P will be available in December through Sharp authorized dealers and value-added resellers for an MSLP of $14,225</p>

<p>For more information, please contact Sharp Electronics Corporation, Professional Display Division, Sharp Plaza, Mahwah, N.J. 07495, or call 866-4-VISUAL (866-484-7825). For online product information, visit the Sharp Professional Display Division Web site at SharpLCD.com or e-mail: ProLCD@SharpUSA.com.</p>

<p>Sharp Electronics Corporation is the U.S. subsidiary of Japan's Sharp Corporation, a worldwide developer of one-of-a-kind home entertainment products, appliances, networked multifunctional office solutions, solar energy solutions and mobile communication and information tools. Leading brands include AQUOS&reg; Liquid Crystal Televisions, 1-Bit&trade; digital audio products, SharpVision&reg; projection products, Insight&reg; Microwave Drawer&reg; appliances, Plasmacluster&reg; air purifiers, and Notevision&reg; multimedia projectors. For more information visit Sharp Electronics Corporation at www.sharpusa.com</p>

<p>Sharp is a registered trademark of Sharp Corporation.</p>

<p>All other trademarks are property of their respective owners. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>November 10, 2008 01:09 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1546
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
 				AND entry_id <> 1546
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/11/sharp_adds_enhanced_inputs_and_network_connectivity_to_professional_high-definition_liquid_crystal_display_lineup.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
