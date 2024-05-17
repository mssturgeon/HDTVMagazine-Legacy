<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 710";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 710 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
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
	<meta name="keywords" content="cinemascope hdht, hdht part, projector lens, anamorphic lens, depending complexity, projector, CinemaScope, cinemascope, could, lens, quality, etc, image, screen, audio, system, video, budget, installation, part, project, professional, components, HDHT, depending" />
	<meta name="description" content="Some people budget for the sky is the limit (or should I say they do not need to budget) when implementing solutions from professional projection systems, like Barco, Sony 4K, etc. Some professional projection systems used for local theaters are in the $100K to $250K range for just the projector.

However, most people are not looking for that level of (expensive) sophistication for regular home theaters. I believe that level of quality offers a low return on the investment considering that a $10K-20K projector could perform very well in the home environment, unless you are looking for 300&quot;+ diagonal screens and a large seating area to compete with George Lucas and the Skywalker Ranch.

Just to put some numbers together, the video part of a CinemaScope&amp;trade; project could require..." />
	<title>HDTV Magazine Articles - CinemaScope&trade; HDHT Part 4 - Budgeting for the Project</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/cinemascope_hdht_part_4_-_budgeting_for_the_project';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('CinemaScope&trade; HDHT Part 4 - Budgeting for the Project'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">CinemaScope&trade; HDHT Part 4 - Budgeting for the Project</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>September 13, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php&amp;phase=2&amp;title=CinemaScope%26trade%3B%20HDHT%20Part%204%20-%20Budgeting%20for%20the%20Project&amp;bodytext=Some%20people%20budget%20for%20the%20sky%20is%20the%20limit%20%28or%20should%20I%20say%20they%20do%20not%20need%20to%20budget%29%20when%20implementing%20solutions%20from%20professional%20projection%20systems%2C%20like%20Barco%2C%20Sony%204K%2C%20etc.%20Some%20professional%20projection%20systems%20used%20for%20local%20theaters%20are%20in%20the%20%24100K%20to%20%24250K%20range%20for%20just%20the%20projector.%0A%0AHowever%2C%20most%20people%20are%20not%20looking%20for%20that%20level%20of%20%28expensive%29%20sophistication%20for%20regular%20home%20theaters.%20I%20believe%20that%20level%20of%20quality%20offers%20a%20low%20return%20on%20the%20investment%20considering%20that%20a%20%2410K-20K%20projector%20could%20perform%20very%20well%20in%20the%20home%20environment%2C%20unless%20you%20are%20looking%20for%20300%22%2B%20diagonal%20screens%20and%20a%20large%20seating%20area%20to%20compete%20with%20George%20Lucas%20and%20the%20Skywalker%20Ranch.%0A%0AJust%20to%20put%20some%20numbers%20together%2C%20the%20video%20part%20of%20a%20CinemaScope%26trade%3B%20project%20could%20require...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the CinemaScope&trade; series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php">CinemaScope&#8482; HDHT - Part 1 - The Concept</a></li>
<li><a href="/articles/2007/03/cinemascope_hdht_-_part_2.php">CinemaScope&#8482; HDHT - Part 2</a></li>
<li><a href="/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></li>
</ul></div>
<br />
Some people budget for the sky is the limit (or should I say they do not need to budget) when implementing solutions from professional projection systems, like Barco, Sony 4K, etc. Some professional projection systems used for local theaters are in the $100K to $250K range for just the projector.

<p><br />
However, most people are not looking for that level of (expensive) sophistication for regular home theaters. I believe that level of quality offers a low return on the investment considering that a $10K-20K projector could perform very well in the home environment, unless you are looking for 300"+ diagonal screens and a large seating area to compete with George Lucas and the Skywalker Ranch.</p>

<p>Just to put some numbers together, the video part of a CinemaScope&trade; project could require the following items:</p>

<p>a) From $5K to $10K for the anamorphic lens/motorized transport depending on the manufacturer. Perhaps budget half of that if the lens would not be transported in and out of the path of the projector lens, an option with pros/cons discussed in the article dedicated to that component.</p>

<p>b) From $1K to $15K on a 2.35:1 screen in the 130" diagonal range, depending on the material; if it is curved, or with motorized masks, if it is flat, if it is Stewart quality or budget constraint quality, etc. The budget could certainly be lower with smaller screen sizes, flat rather than curved, no masking system, and more economic brands.</p>

<p>c) From $4K to $25K for the 1080p projector depending on technology used; if it is DLP, LCoS, LCD, 1-chip, 3-chip, etc. The projector does not have to be 1080p, but a CinemaScope&trade; project is more appreciated with large screens, which require horsepower and resolution from the projector if you like the image to be bright, especially when adding the anamorphic lens in front of the projector lens to expand the image wider.</p>

<p>d) From $2K+ to $6K for a good quality scaler capable of performing the video processing of the incoming image to produce a vertical stretch. That processing interpolates new horizontal lines in between the lines of the letterboxed image, removing the top/bottom black bars of the original 2.35:1 image. Although interpolating those horizontal lines alters the original geometry of the image (making it tall and skinny), it is subsequently restored with the horizontal optical stretch performed by the anamorphic lens when added to the path of light after the projector's lens.</p>

<p>Some video processors/scalers are already encased with the projector, some come paired with the projector as a separate piece, and some home-theater enthusiasts might want to purchase a separate scaler regardless. If the projector already includes a scaler capable of performing a quality CinemaScope&trade; stretch, this item can be removed from the budget.</p>

<p>e) From $500 to $1000 for performing a quality ISF calibration. The final price depends on how cumbersome the projector is to calibrate, and the number of inputs/resolutions you want to calibrate. Although this is not required, it is recommended if the system is expected to perform to its highest potential.</p>

<p>f) From $500 to $1000 on installation parts for the projector, plates for anamorphic lenses and lens transports, long, quality HDMI cabling, wiring for sled control, etc.</p>

<p>g) From $1000 to $3000 on the labor cost for the installation of all the video components of the project (screen, projector, lens, transport, plates, wiring, controls, dedicated power lines, etc.), depending on the complexity of the installation and the easy fit of the components. Audio installation is separate.</p>

<p>h) From $2000 to $5000 for motorized masks and curtains surrounding the screen area depending on the complexity of the system, the shape of the rods, the quantity of fabric, valance or no valance, etc. Although this is not required, masks and curtains improve the appearance of wide-screen images. If the screen item mentioned above comes with its own masking system, this item could be budgeted lower to just cover the remaining parts.</p>

<p>i) From $500 to $2000 for a power line conditioner and quality HD source equipment (Hi Def DVD player, HD tuners, etc) for the HD video signal. Although a movie from a regular DVD player with 2.35:1 aspect ratio upscaled/upconverted to HD resolution also provides a CinemaScope&trade; experience, I recommend that a Hi Def DVD be considered as source equipment due to the quality of the image and the large size of the screen.</p>

<p>Add to the above list the audio components and their installation labor, which many people might have already. As mentioned before, consider the location of the seating area, and the sweet spot for audio and video, so they are as close as they could be. You would not want to view your movie 6 feet away from the good audio sweet spot, especially if at that viewing point of the room the audio has dead zones of standing waves of bass or other audio frequencies that could make dialogue difficult to understand, produce other audio problems, or make the soundtrack uninvolving to viewers at that listening location.</p>

<p>Additional theater components could be added such as seats, Dbox, floor transducers, light controls, dedicated air condition system, room isolation materials, etc., this series of articles does not cover those components.</p>

<p>The list above is just an example to help prepare for this project, some items are required, some are not, for example, I recommend ISF calibration but any system still performs without one, I recommend hiring professional labor to install the parts that could require more than your two hands (and your knowledge), but one could alternatively hire less expensive helpers, or have someone at home helping in manual tasks for which technical HT experience is not needed (like, hey honey, could you please plug that HDMI wire here while I hold the projector in place?), etc.</p>

<p>You are running a risk when doing the job without the proper experience, but sometimes even with a professional you run the risk for things not to go as planned, or as budgeted, especially if that professional never did a CinemaScope&trade; installation before.</p>

<p>Stay tuned for the next article in this CinemaScope&trade; HDHT series.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>September 13, 2007 07:28 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 710
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 710
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
