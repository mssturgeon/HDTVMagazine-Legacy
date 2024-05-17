<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 861";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 861 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dell lounge, media center, digital home, ces dell, inch display, dell, Dell, digital, Lounge, lounge, ces, xps, display, XPS, CES, gaming, inch, home, entertainment, attendees, desktop, laptop, technology, eye, product" />
	<meta name="description" content="Consumer Electronics Show attendees can preview the next wave in digital entertainment, gaming and home networking through an array of innovative concepts, conversations and technology demonstrations at the Dell Lounge. Those not making the annual trek to Las Vegas can tune into www.delllounge.com/ces, where Dell will post webcasts of entertainment events and product demos.

Featured displays include a premium 16-inch concept laptop with a picture-perfect full HD 16:9 aspect ratio and a preview of a mainstream XPS&amp;trade; gaming desktop that will offer performance approaching purpose-built gaming systems. Dell will also demo a high-definition..." />
	<title>HDTV Magazine Bulletins - Dell Lounge Immerses CES Attendees in the Next Wave of High-Def Living</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Dell Lounge Immerses CES Attendees in the Next Wave of High-Def Living'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/01/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Dell Lounge Immerses CES Attendees in the Next Wave of High-Def Living</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  6, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/01/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/01/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/01/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living.php&amp;phase=2&amp;title=Dell%20Lounge%20Immerses%20CES%20Attendees%20in%20the%20Next%20Wave%20of%20High-Def%20Living&amp;bodytext=Consumer%20Electronics%20Show%20attendees%20can%20preview%20the%20next%20wave%20in%20digital%20entertainment%2C%20gaming%20and%20home%20networking%20through%20an%20array%20of%20innovative%20concepts%2C%20conversations%20and%20technology%20demonstrations%20at%20the%20Dell%20Lounge.%20Those%20not%20making%20the%20annual%20trek%20to%20Las%20Vegas%20can%20tune%20into%20www.delllounge.com%2Fces%2C%20where%20Dell%20will%20post%20webcasts%20of%20entertainment%20events%20and%20product%20demos.%0A%0AFeatured%20displays%20include%20a%20premium%2016-inch%20concept%20laptop%20with%20a%20picture-perfect%20full%20HD%2016%3A9%20aspect%20ratio%20and%20a%20preview%20of%20a%20mainstream%20XPS%26trade%3B%20gaming%20desktop%20that%20will%20offer%20performance%20approaching%20purpose-built%20gaming%20systems.%20Dell%20will%20also%20demo%20a%20high-definition...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">Dell Lounge Immerses CES Attendees in the Next Wave of High-Def Living</p>

<p> * Product Demos and Sneak Peeks Include:<br />
 o Concept Laptop with 16-inch Display<br />
 o Future Mainstream Gaming Desktop<br />
 o HD Entertainment Solution with CableCARD and New Extender for Windows Media Center<br />
 * Delllounge.com Will Post Highlights of Happenings from Show Floor<br />
 * Eco-Innovation Booth in Sustainable Technologies TechZone Encourages Conversation on the Meaning of Green</p>

<p>2008 International CES<br />
Dell Lounge #30349<br />
Booth #21854</p>

<p><B>ROUND ROCK, Texas--(BUSINESS WIRE)</B>--Consumer Electronics Show attendees can preview the next wave in digital entertainment, gaming and home networking through an array of innovative concepts, conversations and technology demonstrations at the Dell Lounge. Those not making the annual trek to Las Vegas can tune into www.delllounge.com/ces, where Dell will post webcasts of entertainment events and product demos.</p>

<p>Featured displays include a premium 16-inch concept laptop with a picture-perfect full HD 16:9 aspect ratio and a preview of a mainstream XPS&trade; gaming desktop that will offer performance approaching purpose-built gaming systems. Dell will also demo a high-definition digital home entertainment solution that brings TV to the PC and vice versa, using CableCARD&trade; and the Extender for Windows&reg; Media Center device from Linksys&reg;.</p>

<p>Several new products will make a public appearance in the Dell Lounge, including the colorful Inspiron&trade; 1525 notebook, an award-winning 30-inch UltraSharp&trade; display and the distinctive Crystal flat panel display, which was demonstrated at last year's CES as a concept product.</p>

<p>Dell will also engage members of the ReGeneration - people of all ages who care about the environment -through two illuminated graffiti walls asking "What Does Green Mean to You?" Conference attendees are encouraged to join a conversation with Dell and their peers by writing their ideas and observations on the meaning of "green."</p>

<p><br />
<B>16-inch Display = True 16:9 Aspect Ratio</B></p>

<p>Movie buffs and videophiles are urged to check out a concept XPS laptop with a 16-inch 16:9 aspect display with full HD 1080p resolution. While most laptop available today feature a widescreen display, the true entertainment enthusiast knows that typical 14.1-, 15.4- and 17-inch notebook displays feature a 16:10 ratio versus the 16:9 aspect ratio used in most HDTVs. The most accurate representation of video content for select Blu-ray movies and digital TV is seen with a 16:9 display. When the source video is 16:9, the movie images will fill the entire 16-inch display with no letterboxing.</p>

<p><br />
<B>XPS 630i : Middleweight Contender With Heavyweight Power</B></p>

<p>Starting tomorrow, CES attendees can get a first look at Dell's XPS entry-level gaming desktop, expected to be formally announced this spring with pricing and configuration details. While the XPS 630i is more compact than the powerhouse XPS 720 family, it won't compromise on performance. The XPS 630i can be seen in a variety of locations around CES, including Sony and Creative Labs as well as the Dell Lounge. More about Dell's gaming products is available at www.dell.com/gaming.</p>

<p><br />
<B>Digital Home Demo - HD Entertainment When and Where You Want It</B></p>

<p>Dell's digital home demo showcases convergence of the TV and PC. The solution features an XPS 420 desktop configured with Windows Vista&reg; Ultimate and the ATI TV Wonder&trade; Digital Cable Tuner, enabling users to watch, pause, replay and record premium HD content from digital cable channels on a stunning 30-inch flat panel display. The XPS 420 is paired with a Linksys Media Center Extender (DMA2100), which transfers live or recorded HD video content, digital photos and music across a wired or 802.11n wireless network to any TV in the home. Dell was one of the first to offer CableCARD technology pre-installed on a PC and is partnering with Linksys to launch the DMA2100 Media Center Extender. More about Dell's Digital Home solutions is available at www.dell.com/hometheater.</p>

<p>For those who need help sharing, storing, and managing digital photos, Dell is also demoing one of the hottest new products of the holiday season: the Eye-Fi Card, a wireless SD memory card for digital cameras. Using a home Wi-Fi network, the Eye-Fi Card automatically uploads photos from digital cameras directly to home computers, and to one of 17 online photo sharing, printing, blogging and social networking sites, like Flickr. The Eye-Fi card includes 2GB of memory and free and unlimited uploads to several favorite online sharing sites. More information is available at www.eye.fi..</p>

<p><br />
<B>Gaming Steps on the Accelerator</B></p>

<p>For those who feel the need for speed, Alienware will also be showcasing the new Area-51 m15x notebook, complete with the latest graphics technology from NVIDIA, as well as demoing an ALX desktop with next-generation architecture from Intel. Other demos associated with gaming include the Frag Dolls taking the stage at the Dell Lounge to show off Ubisoft's upcoming Far Cry 2. Dell's unique XPS M1730 World of Warcraft Edition notebook will also be on display.</p>

<p>The Dell Lounge (#30349) is in South Hall 3, second floor. Dell's eco-innovation booth (#21854) is in the Sustainable Technologies TechZone of South Hall 1.</p>

<p><br />
<B>About Dell</B></p>

<p>Dell Inc. (NASDAQ: DELL) listens to customers and delivers innovative technology and services they trust and value. Uniquely enabled by its direct business model, Dell is a leading global systems and services company and No. 34 on the Fortune 500. For more information, visit www.dell.com, or to communicate directly with Dell via a variety of online channels, go to www.dell.com/conversations. To get Dell news direct, visit www.dell.com/RSS.</p>

<p><br />
<B>Additional information:</B></p>

<p> * Direct2Dell - Dell Blogs from CES 2008<br />
 * Dell Vlogs - VLOGS from the show floor<br />
 * Twitter/DellConsumer - up to the minute mini-updates<br />
 * Dell Lounge - Highlights and happenings from the Dell Lounge at CES<br />
 * Second Life - Drop by the Dell Lounge in Second Life<br />
 * Dell's Flickr Page - Product images and more<br />
 * CES Straight Talk - The Official Blog of the International CES</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  6, 2008 03:08 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 861
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
 				AND entry_id <> 861
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/01/dell_lounge_immerses_ces_attendees_in_the_next_wave_of_high-def_living.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
