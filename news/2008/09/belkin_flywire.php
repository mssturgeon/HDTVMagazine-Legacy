<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1539";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1539 AND placement_is_primary = 1";
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
	<meta name="keywords" content="custom installers, flywire offers, remote control, home range, home theater, flywire, FlyWire, installers, belkin, home, Belkin, offers, custom, video, installations, receiver, transmitter, wireless, cables, remote, walls, control, Offers, while, projects" />
	<meta name="description" content="Belkin's new FlyWire, a wireless transmitter that delivers uncompressed 1080p True Cinema HD content, makes home theater and commercial installations quicker and easier, allowing professional integrators to complete jobs more efficiently and take on more projects. This synergy aims to increase profit margins, productivity, and revenue opportunities for installers.

On display at CEDIA Expo 2008, FlyWire wirelessly connects devices-such as Blu-ray&amp;reg; players, receivers, video-game consoles, and set-top boxes-to HDTVs and projectors, transmitting high-definition 1080p True Cinema picture resolution with broad-home range. Featuring..." />
	<title>HDTV Magazine Bulletins - Belkin FlyWire&trade; Offers Faster, Easier, and More Installations to Custom Installers</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Belkin FlyWire&trade; Offers Faster, Easier, and More Installations to Custom Installers'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/09/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Belkin FlyWire&trade; Offers Faster, Easier, and More Installations to Custom Installers</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>September  4, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/09/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/09/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/09/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers.php&amp;phase=2&amp;title=Belkin%20FlyWire%26trade%3B%20Offers%20Faster%2C%20Easier%2C%20and%20More%20Installations%20to%20Custom%20Installers&amp;bodytext=Belkin%27s%20new%20FlyWire%2C%20a%20wireless%20transmitter%20that%20delivers%20uncompressed%201080p%20True%20Cinema%20HD%20content%2C%20makes%20home%20theater%20and%20commercial%20installations%20quicker%20and%20easier%2C%20allowing%20professional%20integrators%20to%20complete%20jobs%20more%20efficiently%20and%20take%20on%20more%20projects.%20This%20synergy%20aims%20to%20increase%20profit%20margins%2C%20productivity%2C%20and%20revenue%20opportunities%20for%20installers.%0A%0AOn%20display%20at%20CEDIA%20Expo%202008%2C%20FlyWire%20wirelessly%20connects%20devices-such%20as%20Blu-ray%26reg%3B%20players%2C%20receivers%2C%20video-game%20consoles%2C%20and%20set-top%20boxes-to%20HDTVs%20and%20projectors%2C%20transmitting%20high-definition%201080p%20True%20Cinema%20picture%20resolution%20with%20broad-home%20range.%20Featuring...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Belkin FlyWire&trade; Offers Faster, Easier, and More Installations to Custom Installers</p>

<center><i>FlyWire, a Wireless HD Transmitter, Helps Custom Installers Reduce Costs While Increasing Productivity by Eliminating the Complication of Labor-Intensive in-Wall Wiring</i></center><br />
<br />

<p><B>COMPTON, Calif.--(BUSINESS WIRE)</B>--Belkin's new FlyWire, a wireless transmitter that delivers uncompressed 1080p True Cinema HD content, makes home theater and commercial installations quicker and easier, allowing professional integrators to complete jobs more efficiently and take on more projects. This synergy aims to increase profit margins, productivity, and revenue opportunities for installers.</p>

<p>On display at CEDIA Expo 2008, FlyWire wirelessly connects devices-such as Blu-ray&reg; players, receivers, video-game consoles, and set-top boxes-to HDTVs and projectors, transmitting high-definition 1080p True Cinema picture resolution with broad-home range. Featuring an intuitive and simple setup, composed of an HD transmitter, receiver, remote control, and IR repeater, FlyWire provides the freedom and flexibility to place an HDTV virtually anywhere-without cables.</p>

<p>FlyWire offers custom installers many options:</p>

<p>    * Allows for greater efficiency on jobs, reducing current project times and enabling installers to offer more services and visit more clients each week<br />
    * Allows for installation of HDTVs in the most challenging locations, including outdoors, such as the patio or backyard<br />
    * Offers the capability to mount TVs above fireplaces and on brick walls without heavy masonry work<br />
    * Reduces labor costs and overall time on the job<br />
    * Eliminates the need to spend days pre-wiring and reconstructing walls and ceilings<br />
    * Simplifies installations-for example, by eliminating the need to crawl under houses or climb into attics to run long-length audio video cables</p>

<p>FlyWire can be installed in various scenarios:</p>

<p>    * Residential<br />
    * Commercial</p>

<p>-- Conference rooms in office buildings<br />
-- Digital signage market, such as sports bars and gyms</p>

<p>    * Schools</p>

<p>AVAILABILITY</p>

<p>    * FlyWire: November 2008 in US, Canada, Europe, Asia</p>

<p>ON DISPLAY AT:</p>

<p>CEDIA Expo 2008, September 3-7, 2008, in Denver, CO, at Booth #260</p>

<p>ABOUT FLYWIRE</p>

<p>As the first offering in the FlyWire family, this solution provides a whole-home range with the capability of penetrating walls and AV cabinets. AV components can be hidden away in a cabinet or a closet, creating an uncluttered living and home-entertainment environment. Installers can also mount projectors on the ceiling without the hassle of cables.</p>

<p>Operating in the open 5GHz band, FlyWire offers a completely robust connection. It intelligently manages its own connection, adjusting both frequency and power to avoid interference and overcome impedances. Because FlyWire does not compress video, it transmits video content with no latency. Even the most demanding AV applications, like video games, will not be impaired by the wireless transmission. FlyWire's SD-card slot also allows for upgrade and expansion options.</p>

<p>FlyWire comes with a transmitter that connects to your AV components (digital HD or analog) and a receiver that mounts to your HDTV, allowing you to place your HDTV in the living room, kitchen, or even outdoors.</p>

<p>FlyWire (AV69003)</p>

<p>    * Offers broad-home range and penetrates walls and windows<br />
    * Includes IR backchannel, allowing you to control AV devices that might be hidden in AV cabinets, closets, or other rooms<br />
    * Includes a remote control for switching between inputs<br />
    * As another option, FlyWire also offers IR receiver and IR blaster attachments to allow you to use your existing remote controls<br />
    * Automatic frequency hopping, which searches for the best available frequency whenever FlyWire detects interference in the area</p>

<p>STATISTICS</p>

<p>    * A 2006 Parks Associates survey found that home theaters are the most common installation with 91% of dealers polled installing home theater systems.<br />
    * The CEA indicates in their January 2008 Industry Forecasts report that sales of flat-panel TVs in 2007 grew year-over-year by 52%.<br />
    * ABI Research projects installations of wireless HDTVs to grow 142% between 2007 and 2013.</p>

<p>QUOTE</p>

<p>Hanoz Gandhi, VP of Products for Belkin International, Inc.:</p>

<p>    * The professional custom integrator recommends the highest-quality electronics and components, while offering installation services like home automation systems and multiroom audio systems that can be highly labor-intensive and challenging.<br />
    * FlyWire maintains the highest levels of video quality and reliability that custom installers demand, while serving as a tool to make their projects more efficient.<br />
    * FlyWire's intuitive and simple setup frees up time for installers to concentrate on other services and additional clients.<br />
    * This increases productivity, margins, and revenue, while providing installers an opportunity to take on more projects and diversify their service offerings.</p>

<p>FlyWire Package Includes:</p>

<p>    * FlyWire Transmitter<br />
    * FlyWire Receiver with Wall-Mounting Bracket<br />
    * FlyWire Remote Control<br />
    * IR Receiver and Blaster<br />
    * (3) HDMI&trade; Cables</p>

<p>About Belkin International, Inc.</p>

<p>Belkin offers a diverse product mix that provides people with seamless electronics integration in their homes, cars, and on the go. Founded in California in 1983, Belkin is a privately held company and the recognized leader in connectivity solutions, in addition to accessories for MP3 devices, computers, and more. We provide extensive solutions for networking, home theater-with our PureAV&reg; line of cables, power, and digital wireless accessories-and many more categories. You can view and purchase them at www.belkin.com, as well as through a network of major distributors, resellers, and superstores.</p>

<p>Belkin's phenomenal growth has led to our expanding global presence. In addition to corporate headquarters in Los Angeles, we now have offices throughout Europe-in the United Kingdom, the Netherlands, and Germany, among others-and in the Asia Pacific region, including Australia, Shanghai, and our regional headquarters in Hong Kong. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>September  4, 2008 02:31 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1539
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
 				AND entry_id <> 1539
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/09/belkin_flywire_offers_faster_easier_and_more_installations_to_custom_installers.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
