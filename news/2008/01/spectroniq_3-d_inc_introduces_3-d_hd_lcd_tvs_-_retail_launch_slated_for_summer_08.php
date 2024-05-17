<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 884";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 884 AND placement_is_primary = 1";
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
	<meta name="keywords" content="lcd tvs, sensio technologies, kerner optical, consumer electronics, technologies inc, spectroniq, SPECTRONIQ, technology, technologies, sensio, Inc, inc, SENSIO, tvs, TVs, lcd, company, LCD, kord, trademark, KORD, Technologies, california, ilm, California" />
	<meta name="description" content="Southern California-based consumer electronics innovator SPECTRONIQ(R) 3-D Inc. (&quot;SPECTRONIQ 3-D&quot;), will unveil its inaugural stereoscopic 3-D HD LCD TVs in Las Vegas as the focal point of the company's presentation during CES, January 7-10. Already a leader in the realm of high-end HD LCD televisions, SPECTRONIQ 3-D is ramping up for a groundbreaking summer 2008 nationwide retail launch for the 46&quot; 3-D TVs under the SPECTRONIQ(R) brand, the first mass market consumer roll-out of its kind.

At the vanguard of setting a new industry standard for..." />
	<title>HDTV Magazine Bulletins - SPECTRONIQ 3-D, Inc. Introduces 3-D HD LCD TVs - Retail Launch Slated for Summer '08</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('SPECTRONIQ 3-D, Inc. Introduces 3-D HD LCD TVs - Retail Launch Slated for Summer \'08'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">SPECTRONIQ 3-D, Inc. Introduces 3-D HD LCD TVs - Retail Launch Slated for Summer '08</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  7, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08.php&amp;phase=2&amp;title=SPECTRONIQ%203-D%2C%20Inc.%20Introduces%203-D%20HD%20LCD%20TVs%20-%20Retail%20Launch%20Slated%20for%20Summer%20%2708&amp;bodytext=Southern%20California-based%20consumer%20electronics%20innovator%20SPECTRONIQ%28R%29%203-D%20Inc.%20%28%22SPECTRONIQ%203-D%22%29%2C%20will%20unveil%20its%20inaugural%20stereoscopic%203-D%20HD%20LCD%20TVs%20in%20Las%20Vegas%20as%20the%20focal%20point%20of%20the%20company%27s%20presentation%20during%20CES%2C%20January%207-10.%20Already%20a%20leader%20in%20the%20realm%20of%20high-end%20HD%20LCD%20televisions%2C%20SPECTRONIQ%203-D%20is%20ramping%20up%20for%20a%20groundbreaking%20summer%202008%20nationwide%20retail%20launch%20for%20the%2046%22%203-D%20TVs%20under%20the%20SPECTRONIQ%28R%29%20brand%2C%20the%20first%20mass%20market%20consumer%20roll-out%20of%20its%20kind.%0A%0AAt%20the%20vanguard%20of%20setting%20a%20new%20industry%20standard%20for...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">SPECTRONIQ 3-D, Inc. Introduces Pioneering 3-D HD LCD TVs During CES in Las Vegas on Jan. 7 - Retail Launch Slated for Summer '08</p>

<center><i>46" Sets Employ Proprietary Kerner Optical Research & Development 3-D Solutions and Incorporate Technologies from Sensio Technologies(R) and RaisingSun</i></center><br />
<br />

<p><B>LOS ANGELES, Jan. 7 /PRNewswire-FirstCall/ </B>-- Southern California-based consumer electronics innovator SPECTRONIQ(R) 3-D Inc. ("SPECTRONIQ 3-D"), will unveil its inaugural stereoscopic 3-D HD LCD TVs in Las Vegas as the focal point of the company's presentation during CES, January 7-10. Already a leader in the realm of high-end HD LCD televisions, SPECTRONIQ 3-D is ramping up for a groundbreaking summer 2008 nationwide retail launch for the 46" 3-D TVs under the SPECTRONIQ(R) brand, the first mass market consumer roll-out of its kind.</p>

<p>At the vanguard of setting a new industry standard for the coming 3-D revolution in digital media, entertainment and gaming, SPECTRONIQ 3-D's 3-D HD LCD TVs deliver an immersive and riveting sensory experience. Visual elements appear to break through the screen's boundaries and the technology allows for a spacious 3-D viewing zone. Proprietary in both its methodology of combining diverse cutting-edge technologies -- universally compatible with all current 3-D formats -- and with its intuitive user interface applications, SPECTRONIQ 3-D has created a user-friendly product unprecedented in the marketplace.</p>

<p>SPECTRONIQ 3-D's CEO Leo Chen commented, "The impact of 3-D on consumer electronics is going to be the biggest paradigm shift in decades. It is an extraordinary feeling to be on the brink of introducing this technology and making it available and affordable, to home audiences for the very first time."</p>

<p>The sets were developed through a landmark alliance between SPECTRONIQ 3-D and Kerner Optical Research & Development Inc. ("KORD"), whose end-to-end 3-D Solutions form the SPECTRONIQ 3-D TV's primary technology suite. KORD is a spin-off of Kerner Optical, LLC, the former physical effects division of Industrial Light & Magic(R) ("ILM(R)") a Lucasfilm(R) Company. SPECTRONIQ 3- D's sophisticated consumer electronics design, production, distribution, and sales chain, together with KORD's brilliance as a disruptive technology incubator, positions both to lead the way in bringing breakthrough 3-D display technology and original content to the masses.</p>

<p>Based in Northern California on the historic former ILM campus, KORD's core team includes members of George Lucas' ILM start-up brain trust as well as noted stereoscopic 3-D experts. Collectively, they bring with them a fifteen-year track record of pioneering stereoscopic 3-D technologies - as well as expertise that contributed to ILM's 15 Academy Awards(R) for films including E.T.: The Extra-Terrestrial, Indiana Jones & the Temple of Doom and Star Wars, Episodes V and VI. In addition to 3-D display technology, KORD and its affiliates have also developed an unparalleled 3-D camera rig where SPECTRONIQ 3-D is further collaborating with them on the development of exclusive content.</p>

<p>SPECTRONIQ 3-D has also announced other key strategic partners whose technologies are intrinsic to the viewing experience provided by their 3-D HD LCD TVs:</p>

<p>-- An agreement was reached with SENSIO Technologies to incorporate the Montreal-based company's 3D high definition decoder technology featuring JVC's real time 2D to 3D conversion technology, into KORD's 3-D Solutions suite. This will be the first time it will be integrated into a television intended for the consumer market. SENSIO technology provides quality 3D technology with full resolution and colors.<br />
-- Technology firm RaisingSun Digital Video Technology Company is providing board systems solutions for the SPECTRONIQ 3-D television sets.<br />
-- It is expected that other key partnerships will be made public in the near future.</p>

<p><br />
In addition to 3-D HD LCD TVs, SPECTRONIQ 3-D products in development also include a 3-D home theater PC for gaming and a laser HD television. On Monday, January 7, 2008, CES's first convention night, SPECTRONIQ 3-D will celebrate its landmark 3-D TV launch with a four-walled event at The Joint at the Hard Rock Hotel & Casino.</p>

<p> For more information, log on to: http://www.spectroniq3-d.com/</p>

<p><B>About SPECTRONIQ 3-D:</B></p>

<p>Under Leo Chen's leadership, SPECTRONIQ 3-D, Inc. has leveraged both its advantages as a streamlined, independent company able to quickly address new trends and technologies and its close ties with many of the largest manufacturers in the U.S. and Asia to meet product demands. Prior to founding SPECTRONIQ 3-D, Chen's background includes tenures with IBM and DAHW. Born in Shanghai, Chen has a degree in Civil Engineering from China's Tongji University, and holds an MBA in International Trade at Columbia University. In 2007, he received the prestigious Ellis Island Medal of Honor which, since 1986, has been bestowed upon "American citizens of diverse origins for their outstanding contributions to their communities, their nation and the world." Officially recognized by both U.S. Houses of Congress, all recipients of the award -- which include six U.S. Presidents -- are listed in the Congressional Record.</p>

<p>About SENSIO Technologies Inc. (SENSIO): Founded in 1999, SENSIO Technologies Inc. (TSX.V: SIO), headquartered in Montreal, Canada, develops and markets forward-looking stereoscopic technologies designed to offer the most advanced and immersive cinematographic experience available. Its flagship technology, SENSIO(R)3D, allows the high-quality distribution of 3D content through conventional 2D channels and playback on any display device, including plasma TVs, HDTV and glass-free 3D displays. Working with major Hollywood studios and large format 3D film producers, SENSIO has built up one of the world's largest library of 3D movies for the home entertainment market. SENSIO(R) is a trademark of SENSIO Technologies Inc.</p>

<p>"SPECTRONIQ" and "THE STANDARD FOR 3D TV" are the trademarks of Spectroniq Trademark Holding, LLC, a California limited liability company, and their uses are granted under license from Spectroniq Trademark Holding, LLC.</p>

<p>"Sensio" is the trademark of Les Technologies Sensorielles (TEG) Inc., a Canada corporation.</p>

<p>"RaisingSun" is the trademark of RaisingSun Digital Video Technology Company of Shanghai, China.</p>

<p>"Industrial Light & Magic", "ILM", "Lucasfilm", "Indiana Jones" and "Star Wars" are the trademarks of Lucasfilm, Ltd., a California corporation.</p>

<p>"KORD" is the trademark of Kerner Optical Research & Development Inc.</p>

<p>Academy Awards is the trademark of ACADEMY OF MOTION PICTURE ARTS AND SCIENCES, a California corporation.</p>

<p>Source: SPECTRONIQ 3-D, Inc.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  7, 2008 08:05 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 884
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
 				AND entry_id <> 884
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/spectroniq_3-d_inc_introduces_3-d_hd_lcd_tvs_-_retail_launch_slated_for_summer_08.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
