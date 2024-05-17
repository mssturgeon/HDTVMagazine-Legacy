<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 609";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 609 AND placement_is_primary = 1";
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
	<meta name="keywords" content="frame rate, high frame, inch kds, kds xbr, sony electronics, sony, Sony, models, kds, KDS, high, video, bravia, new, BRAVIA, feature, digital, xbr, display, SXRD, internet, XBR, including, Internet, sxrd" />
	<meta name="description" content="Sony today unveiled five new BRAVIA(R)SXRD(R) (Silicon X-tal (Crystal) Reflective Display) micro-display HDTVs featuring Motionflow 120Hz high frame rate technology and dramatically thinner cabinets.

About 20 percent slimmer than last year's sets, Sony's A3000 line features three new models including the 50-inch KDS-50A3000, the 55-inch KDS-55A3000, the 60-inch KDS-60A3000 (screen sizes measured diagonally) models." />
	<title>HDTV Magazine Bulletins - Sony Introduces Slim SXRD Micro-Displays With High Frame Rate</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Sony Introduces Slim SXRD Micro-Displays With High Frame Rate'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2007/06/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Sony Introduces Slim SXRD Micro-Displays With High Frame Rate</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>June  6, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/06/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2007/06/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2007/06/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2007/06/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate.php&amp;phase=2&amp;title=Sony%20Introduces%20Slim%20SXRD%20Micro-Displays%20With%20High%20Frame%20Rate&amp;bodytext=Sony%20today%20unveiled%20five%20new%20BRAVIA%28R%29SXRD%28R%29%20%28Silicon%20X-tal%20%28Crystal%29%20Reflective%20Display%29%20micro-display%20HDTVs%20featuring%20Motionflow%20120Hz%20high%20frame%20rate%20technology%20and%20dramatically%20thinner%20cabinets.%0A%0AAbout%2020%20percent%20slimmer%20than%20last%20year%27s%20sets%2C%20Sony%27s%20A3000%20line%20features%20three%20new%20models%20including%20the%2050-inch%20KDS-50A3000%2C%20the%2055-inch%20KDS-55A3000%2C%20the%2060-inch%20KDS-60A3000%20%28screen%20sizes%20measured%20diagonally%29%20models.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Sony Introduces Slim SXRD Micro-Displays With High Frame Rate</p>

<center><i>New Sets are 20-40 Percent Slimmer Than Previous Models</i></center><br />
<br />
<img src="/images/products/sony-slim-sxrd.jpg" align="left" /><B>NEW YORK, June 6 /PRNewswire/</B> -- Sony today unveiled five new BRAVIA(R)SXRD(R) (Silicon X-tal (Crystal) Reflective Display) micro-display HDTVs featuring Motionflow 120Hz high frame rate technology and dramatically thinner cabinets.

<p>About 20 percent slimmer than last year's sets, Sony's A3000 line features three new models including the 50-inch KDS-50A3000, the 55-inch KDS-55A3000, the 60-inch KDS-60A3000 (screen sizes measured diagonally) models.</p>

<p>With cabinets about 40 percent thinner than its predecessor models, the XBR micro-display line features two new sets -- the 60-inch KDS-Z60XBR5 and 70-inch KDS-Z70XBR5 (screen sizes measured diagonally). Sony's engineers were able to decrease the depth of each model without increasing the height of the models.</p>

<p>"Going bigger is not a compromise with Sony SXRD micro-displays," said Randy Waynick, senior vice president of Sony Electronics' Home Products Division. "With slim cabinets, advanced features like high frame rate and full HD 1080p resolution, SXRD TVs give consumers who want big picture impact exactly what they have been looking for."</p>

<p>Key to the outstanding picture quality is Sony's Motionflow 120Hz high frame rate technology. The feature is adjustable with four modes (including "off") that the user can change to match the content, be it high-definition sports or Blu-ray Disc(R) movies.</p>

<p>Enhancing image quality further is the adoption of the new industry color standard for video, xvYCC, also referred to as x.v.Color(TM) technology. This standard expands the potential color data range of video by about 1.8 times resulting in the display of more natural and vivid colors similar to what the human eye can actually see with supporting video sources such as select Sony camcorders.</p>

<p>Developed in consultation with the Sony Pictures Entertainment movie studio, the new BRAVIA HDTVs feature Theater Mode that adjusts the TV to display movies better preserving the mood and detail that the filmmaker intended. When the Theater button on the television's remote control is selected, the TV automatically adjusts settings to one that has been specifically optimized for BRAVIA LCD and SXRD display technology. Sony's Theater Mode is the result of consultation with the people who bring Blu-ray disc and DVD for home viewing to reproduce an exceptional cinematic experience of the movie theater in your living room.</p>

<p>BRAVIA TVs are not just optimized for movies, however. More and more people want to view photos from their compatible digital cameras on their large-screen HDTV, expanding beyond just viewing them on computer monitors. The new PhotoTV HD mode brings the look of actual printed photography to the set, reproducing high quality digital photos by fine-tuning parameters, including sharpness, gradation and color.</p>

<p>The TVs are compatible with Sony's Digital Media Extender (DMeX) offering a digital connection path for the BRAVIA Internet Video Link module (sold separately). Announced earlier this year, the BRAVIA Internet Video Link, allows users to view select Internet video content, including high-definition, from providers AOL, Yahoo! and Grouper, as well as Sony Pictures Entertainment and Sony BMG Music.</p>

<p>The module mounts on the back of a compatible Sony TV and connects directly to the Internet via the users existing broadband Ethernet connection (3 Mbps or greater) without the use of a personal computer. The feature will give users access to select Internet video, music videos, movie trailers, user generated videos and RSS feeds without additional charges.</p>

<p>Providing easy access to Internet videos as well as user and set up menus on most of Sony's new models is the Emmy(R) Award-winning Xross Media Bar(TM) (XMB) interface. The XMB incorporates a simple graphic user interface to help users maneuver through the menu systems easily and quickly.</p>

<p>Also simplifying operation is Sony's BRAVIA Theatre Sync(TM) feature simplifies the situation with its one-button command, which integrates the operation of the television with supporting external components connected via an HDMI(TM) input (based on industry standard HDMI-CEC). Through a one-button click of the remote, users can easily enjoy viewing a Blu-ray Disc(TM) movie, listening to surround sound audio via an AV receiver, and controlling other components, all over just one single cable connection avoiding the hassle of powering on individual components, changing AV receiver audio input, switching TV video inputs, etc. (HDMI cable sold separately.)</p>

<p>For content that does not match the razor sharp resolution and rich colors available on a Blu-ray disc or a high-definition broadcast, the A3000 models feature Sony's BRAVIA Engine(TM) EX full digital video processing system with Digital Reality Creation-Multifunction v1.0. The technology up-converts all non-HD signal to 1080i, delivering an exceptional image to the screen.</p>

<p>The XBR models also add BRAVIA Engine Pro circuitry with Digital Reality Creation-MultiFunction v2.5 which up-converts signals, including 720p and 1080i, to 1080p.</p>

<p>When high-definition signals are available, however, all of the models feature 1080p input capability via HDMI, component, and PC inputs (with supporting PC graphics cards) for an outstanding picture. Additionally, the HD component and HDMI inputs are compatible with both 1080/60p and 1080/24p sources.</p>

<p>Adding the ability to personalize for home decor, the A3000 models also feature an interchangeable speaker grille, which can be swapped out for one of four optional colors including Burgundy Red, Metallic Silver, Satin Black and Cacao. The speaker grills are sold separately for about $50 each.</p>

<p>The KDS-50A3000, KDS-55A3000 and KDS-60A3000 models debut in August for about $3,000, $3,300 and $3,500, respectively. The KDS-Z60XBR5 and KDS-Z70XBR5 ship this fall for about $5,000 and $6,000 respectively.</p>

<p>All models will be offered direct at sonystyle.com and Sony Style retail stores nationwide, as well as at military base exchanges and authorized dealers around the country.<br />
Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20070606/LAW130<br />
AP Archive: http://photoarchive.ap.org/<br />
AP PhotoExpress Network: PRN13<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: Sony Electronics, Inc.</p>

<p>CONTACT: Greg Belloni of Sony Electronics, Inc., +1-858-942-4460,<br />
greg.belloni@am.sony.com; or Tania Scheer of PainePR, +1-212-613-4918,<br />
tscheer@painepr.com, for Sony Electronics, Inc.</p>

<p>Web site: http://www.sony.com/news</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>June  6, 2007 03:13 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 609
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
 				AND entry_id <> 609
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2007/06/sony_introduces_slim_sxrd_micro-displays_with_high_frame_rate.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
