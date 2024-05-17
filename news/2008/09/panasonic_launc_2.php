<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1524";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1524 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, panasonic projector, projector systems, theater projector, motion images, projector, panasonic, theater, Panasonic, home, images, Projector, frame, systems, new, high, screen, frames, color, company, power, technology, brightness, users, unit" />
	<meta name="description" content="Panasonic Projector Systems Company, Unit of Panasonic Corporation of North America, announced today the debut of its newest LCD home theater projector, the PT-AE3000. With full high-definition 1080p (1,920 x 1,080 pixel) native resolution, the PT-AE3000 gives home theater enthusiasts a higher level of picture quality entertainment, producing crisp and clear images through new state-of-the-art projection technology.

As an evolution of its award winning predecessor, the PT-AE2000, the PT-AE3000 delivers a captivating..." />
	<title>HDTV Magazine Bulletins - Panasonic Launches New 1080p Full High-Definition LCD Home Theater Projector</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic Launches New 1080p Full High-Definition LCD Home Theater Projector'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic Launches New 1080p Full High-Definition LCD Home Theater Projector</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  3, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector.php&amp;phase=2&amp;title=Panasonic%20Launches%20New%201080p%20Full%20High-Definition%20LCD%20Home%20Theater%20Projector&amp;bodytext=Panasonic%20Projector%20Systems%20Company%2C%20Unit%20of%20Panasonic%20Corporation%20of%20North%20America%2C%20announced%20today%20the%20debut%20of%20its%20newest%20LCD%20home%20theater%20projector%2C%20the%20PT-AE3000.%20With%20full%20high-definition%201080p%20%281%2C920%20x%201%2C080%20pixel%29%20native%20resolution%2C%20the%20PT-AE3000%20gives%20home%20theater%20enthusiasts%20a%20higher%20level%20of%20picture%20quality%20entertainment%2C%20producing%20crisp%20and%20clear%20images%20through%20new%20state-of-the-art%20projection%20technology.%0A%0AAs%20an%20evolution%20of%20its%20award%20winning%20predecessor%2C%20the%20PT-AE2000%2C%20the%20PT-AE3000%20delivers%20a%20captivating...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Panasonic Launches New 1080p Full High-Definition LCD Home Theater Projector</p>

<center><i>New PT-AE3000 Provides A Bright and Dynamic Widescreen Home Viewing Experience with Crisp Motion Images Via Frame Creation Technology

<p>CEDIA EXPO 2008</i></center><br /><br />
<br /></p>

<p><B>DENVER--(BUSINESS WIRE)</B>--Panasonic Projector Systems Company, Unit of Panasonic Corporation of North America, announced today the debut of its newest LCD home theater projector, the PT-AE3000. With full high-definition 1080p (1,920 x 1,080 pixel) native resolution, the PT-AE3000 gives home theater enthusiasts a higher level of picture quality entertainment, producing crisp and clear images through new state-of-the-art projection technology.</p>

<p>As an evolution of its award winning predecessor, the PT-AE2000, the PT-AE3000 delivers a captivating 1,600 lumen brightness and powerful 60,000:1 contrast ratio. To achieve sharp and detailed reproduction of fast moving motion images, Panasonic has developed and equipped the PT-AE3000 with new Frame Creation Technology optimized for large screen viewing. And for easy adjustment to different widescreen movie formats like 21:9 or 16:9, the projector is also upgraded with a Lens Memory Load feature which is capable of memorizing and restoring zoom/focus positions for multiple aspect ratio flexibility.</p>

<p><br />
<B>Higher Picture Quality with Brightness Upgrade</B></p>

<p>The dramatic increase in the contrast-ratio and dynamic range was made possible by the newly engineered Pure Contrast Plate technology that effectively blocks unwanted light from the path of projection. In addition, the polarization filter system surrounding the Pure Contrast Plates has been redesigned to achieve the PT-AE3000's higher level of brightness.</p>

<p>Together with a host of carefully engineered Panasonic technologies including the high precision optical lens unit, Dynamic Iris and Smooth Screen technology, the PT-AE3000 truly distinguishes itself against its competition and as a projector solution that home theater enthusiasts will thoroughly enjoy.</p>

<p>"Each year with advances in our AE Home Theater series, we continue to strive for the best 'real theater' environment for our customers," said Rena Yotsu, home theater specialist, Projector Business Unit, Panasonic AVC Networks Company. "To reach this goal, Panasonic Projector Systems Company engineers have designed many innovative technologies and have collaborated side-by-side with Hollywood colorists and image experts to manufacture our outstanding, upgraded PT-AE3000 home theater projector."</p>

<p><br />
<B>Crisp and Clear Details for In-Motion Images</B></p>

<p>The PT-AE3000 now incorporates a Frame Creation technology that interpolates an additional frame by analyzing the characteristics of two adjacent frames, creating sharp and clear images of fast moving scenes in sports and action movies, especially for large screen viewing. For crisp motion images with high resolution, users may set their frame count preference to 120 frames/1 sec from 60 frames/1 sec for 60Hz video signal input, while 24frames/sec (24p) signals are quadrupled to 96frames/1sec by interpolating three additional frames to allow natural frame-to-frame transitions.</p>

<p>The new and advanced Detail Clarity Processor V2.0 allows the projectors' digital processing engine to extract information of the low, mid, high, and even the super-high frequency components. The ability to apply these different degrees of sharpness permits the PT-AE3000 to reproduce natural, lifelike images with exceptional clarity and three-dimensionality to the smallest details.</p>

<p>The PT-AE3000 comes equipped with professional-level features that let users personalize the images they see on the screen. First, the projector's Split Adjust mode allows users to split the screen into two halves; one to make picture adjustments and one to see those adjustments as a baseline reference. Second, the PT-AE3000's waveform monitor-seen mostly on professional video and film equipment-measures the level of brightness output from a given video signal source. This feature also automatically verifies that the source device is outputting at full dynamic range, and quickly makes adjustments to optimize it for the home theater setting. Users can easily see a graphical representation of any changes made to the unit's color settings.</p>

<p><br />
<B>Ease of Set-up and Use</B></p>

<p>Many movies come in aspects that are even wider than the projector's 16:9 aspect ratio to match the image size 21:9 seen at movie theaters. More and more projector customers are using the 21:9 wide screens to mirror the movie theater experience. The PT-AE3000 enables users to pre-set three different zoom/focus positions and enables easy recall of those positions with the Lens Memory Load function.</p>

<p>Initial set-up and installation are quick and simple, as the 2.0x zoom can cast a 120-inch diagonal image from as short as 3.6 meters (about 11 feet) to 7 meters (24 feet) from the screen. The projector's lens shift capability enables 100 percent vertical adjustment and 40 percent horizontal adjustment so that viewers can suspend the projector either close to the ceiling or on wall mounts. The lens-centered design and maintenance features such as the replaceable side filter and top lamp, make the projector easy to install and maintain.</p>

<p>The projector is also equipped with an abundant selection of connections; including three HDMI 1.3 inputs and two component input terminals. The three HDMI 1.3 inputs are all Deep Color and x.v.Color compliant. The Deep Color supports 10 bit (over 1.07 billion) and 12 bit (over 68.7 billion) color depths for smooth tonal transitions and subtle gradations between colors, while the x.v.Color compliance realizes accurate reproduction of actual, true-to-life images.</p>

<p><br />
<B>Eco-Friendly Intelligence</B></p>

<p>In addition to its outstanding features, the new PT-AE3000 is also an ecologically conscious product. The intelligent power management system within the projector reduces the lamp power to the exact required level of brightness depending on what content is displayed. Other than in scenes where full brightness is required, the lamp intelligently determines the necessary power output by analyzing more than 3 billion different image patterns. This advanced analysis process reduces the main power consumption by as much as 10 percent when the dynamic iris function is in operation, thus saving energy.</p>

<p>Other ecological considerations include an off-timer that reduces wasteful power consumption, as well as an extremely low standby power consumption of 0.08 W1, which is the lowest in its class.2</p>

<p>The Panasonic PT-AE3000 LCD home theater projector will be made in Japan, available starting in October 2008 to authorized presentation systems dealers with an MSRP of $3,499. To learn more about this projector, please visit http://www.panasonic.com/projectors or http://panasonic.net/pavc/projector/.</p>

<p><br />
<B>About Panasonic Projector Systems Company</B></p>

<p>Panasonic Projector Systems Company is a prominent supplier of projectors and presentation systems in the United States. It is a unit of Panasonic Corporation of North America, the principal North American subsidiary of Matsushita Electric Industrial Co., Ltd. (NYSE:MC) of Japan, and the hub of its U.S. marketing, sales, service and R&D operations. For more information on Panasonic Projector Systems products, visit the company's Web site at www.panasonic.com/projectors.</p>

<p>1 Up to 220 V.</p>

<p>2 For 1080p home theater projectors as of August 18, 2008. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  3, 2008 10:31 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1524
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
 				AND entry_id <> 1524
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/panasonic_launches_new_1080p_full_high-definition_lcd_home_theater_projector.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
