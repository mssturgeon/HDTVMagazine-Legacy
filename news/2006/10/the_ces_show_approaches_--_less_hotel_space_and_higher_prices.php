<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 460";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 460 AND placement_is_primary = 1";
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
	<meta name="keywords" content="sands venetian, international ces, tech ones, innovations plus, las vegas, ces, CES, sands, Sands, venetian, Venetian, press, Innovations, innovations, tech, Tech, international, International, ones, events, year, technology, vegas, Las, Vegas" />
	<meta name="description" content="What the following press bulletin doesn't note is the fact that fewer hotel rooms will be available in Las Vegas this January due the  tearing down of old hotels in preparation for building new ones. Hotel prices are also on the rise along with the new brick and morter. Consumer Electronics CEO, Gary Shapiro, said yesterday in San Francisco that in order to increase the quality of those who do come to the CES the entry fee will be raised from $150 to $200. They anticipate 140,000, a number slightly less than last year's record.   



2007 INTERNATIONAL CES EXPANDS TO SANDS/VENETIAN FOR ALL NEW LINEUP OF MUST-SEE EVENTS 

&lt;em&gt;Keynotes, High-Performance Audio and Home Theater, Innovations Plus, TechZones, CES Unveiled and Press Conferences among Venue Highlights&lt;/em&gt;

Arlington, Va., October 17, 2006 - CES keynotes, high-performance audio and home theater exhibitors, Innovations honorees, emerging technology exhibits, TechZones, and a range of special media events take center stage at the newest venue of the 2007 International Consumer Electronics Show (CES®) - Innovations Plus at the Sands. To accommodate these must-see events, CES is augmenting the Las Vegas Convention Center (LVCC) with halls B, C and D of the Sands Expo and Convention Center and portions of the adjoining Venetian Hotel. The 2007 International CES, the world's largest annual consumer technology tradeshow, runs January 8-11, in Las Vegas, Nevada. " />
	<title>HDTV Magazine Bulletins - The CES Show Approaches --  Less Hotel Space and Higher Prices</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/the_ces_show_approaches_--_less_hotel_space_and_higher_prices';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('The CES Show Approaches --  Less Hotel Space and Higher Prices'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/10/the_ces_show_approaches_--_less_hotel_space_and_higher_prices.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">The CES Show Approaches --  Less Hotel Space and Higher Prices</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>October 17, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/10/the_ces_show_approaches_--_less_hotel_space_and_higher_prices.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2006/10/the_ces_show_approaches_--_less_hotel_space_and_higher_prices.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/10/the_ces_show_approaches_--_less_hotel_space_and_higher_prices.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/10/the_ces_show_approaches_--_less_hotel_space_and_higher_prices.php&amp;phase=2&amp;title=The%20CES%20Show%20Approaches%20--%20%20Less%20Hotel%20Space%20and%20Higher%20Prices&amp;bodytext=What%20the%20following%20press%20bulletin%20doesn%27t%20note%20is%20the%20fact%20that%20fewer%20hotel%20rooms%20will%20be%20available%20in%20Las%20Vegas%20this%20January%20due%20the%20%20tearing%20down%20of%20old%20hotels%20in%20preparation%20for%20building%20new%20ones.%20Hotel%20prices%20are%20also%20on%20the%20rise%20along%20with%20the%20new%20brick%20and%20morter.%20Consumer%20Electronics%20CEO%2C%20Gary%20Shapiro%2C%20said%20yesterday%20in%20San%20Francisco%20that%20in%20order%20to%20increase%20the%20quality%20of%20those%20who%20do%20come%20to%20the%20CES%20the%20entry%20fee%20will%20be%20raised%20from%20%24150%20to%20%24200.%20They%20anticipate%20140%2C000%2C%20a%20number%20slightly%20less%20than%20last%20year%27s%20record.%20%20%20%0A%0A%0A%0A2007%20INTERNATIONAL%20CES%20EXPANDS%20TO%20SANDS%2FVENETIAN%20FOR%20ALL%20NEW%20LINEUP%20OF%20MUST-SEE%20EVENTS%20%0A%0A%3Cem%3EKeynotes%2C%20High-Performance%20Audio%20and%20Home%20Theater%2C%20Innovations%20Plus%2C%20TechZones%2C%20CES%20Unveiled%20and%20Press%20Conferences%20among%20Venue%20Highlights%3C%2Fem%3E%0A%0AArlington%2C%20Va.%2C%20October%2017%2C%202006%20-%20CES%20keynotes%2C%20high-performance%20audio%20and%20home%20theater%20exhibitors%2C%20Innovations%20honorees%2C%20emerging%20technology%20exhibits%2C%20TechZones%2C%20and%20a%20range%20of%20special%20media%20events%20take%20center%20stage%20at%20the%20newest%20venue%20of%20the%202007%20International%20Consumer%20Electronics%20Show%20%28CES%C2%AE%29%20-%20Innovations%20Plus%20at%20the%20Sands.%20To%20accommodate%20these%20must-see%20events%2C%20CES%20is%20augmenting%20the%20Las%20Vegas%20Convention%20Center%20%28LVCC%29%20with%20halls%20B%2C%20C%20and%20D%20of%20the%20Sands%20Expo%20and%20Convention%20Center%20and%20portions%20of%20the%20adjoining%20Venetian%20Hotel.%20The%202007%20International%20CES%2C%20the%20world%27s%20largest%20annual%20consumer%20technology%20tradeshow%2C%20runs%20January%208-11%2C%20in%20Las%20Vegas%2C%20Nevada.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>What the following press bulletin doesn't note is the fact that fewer hotel rooms will be available in Las Vegas this January due the  tearing down of old hotels in preparation for building new ones. Hotel prices are also on the rise along with the new brick and mortar. Consumer Electronics CEO, Gary Shapiro, said yesterday in San Francisco that in order to increase the quality of those who do come to the CES the entry fee will be raised from $150 to $200. They anticipate 140,000, a number slightly less than last year's record.   </p>

<p><br />
<strong>2007 INTERNATIONAL CES EXPANDS TO SANDS/VENETIAN FOR ALL NEW LINEUP OF MUST-SEE EVENTS </strong></p>

<p><em>Keynotes, High-Performance Audio and Home Theater, Innovations Plus, TechZones, CES Unveiled and Press Conferences among Venue Highlights</em></p>

<p>Arlington, Va., October 17, 2006 - CES keynotes, high-performance audio and home theater exhibitors, Innovations honorees, emerging technology exhibits, TechZones, and a range of special media events take center stage at the newest venue of the 2007 International Consumer Electronics Show (CES®) - Innovations Plus at the Sands. To accommodate these must-see events, CES is augmenting the Las Vegas Convention Center (LVCC) with halls B, C and D of the Sands Expo and Convention Center and portions of the adjoining Venetian Hotel. The 2007 International CES, the world's largest annual consumer technology tradeshow, runs January 8-11, in Las Vegas, Nevada. </p>

<p>This year, keynotes will move from their previous location at the Las Vegas Hilton to the more spacious Palazzo Ballroom at the Sands/Venetian. The star-studded lineup of CE industry leaders will feature Bill Gates, chairman, Microsoft; Ed Zander, chairman and CEO, Motorola Inc.; Robert Iger, president and CEO, The Walt Disney Co.; and Michael Dell, chairman, Dell Inc. </p>

<p>In addition, more than 200 high-performance audio and home theater (HPAHT) exhibitors, including, MBL of America, Musical Surroundings, Sumiko and Thiel will exhibit at the Sands/Venetian. </p>

<p>"For the second year in a row, the Sands is a must-visit venue at the International CES, and we're expecting equal or greater popularity with the addition of The Venetian to our venue lineup," said Karen Chupka, senior vice president of events and conferences at CEA, the producer of the CES. "CES will continue to showcase the latest emerging technologies, award honorees and expert speakers and will build on this success by offering an even more powerful lineup of exhibitors, events and speakers at the Sands/Venetian." </p>

<p>The Sands/Venetian will again be home to Innovations Plus, which will feature more than 12 emerging technology related TechZones, the International CES Innovations Honorees displays, a dozen conference sessions, select CES Partner Programs, and live radio broadcasts from Dave Graveline's "Into Tomorrow" program. </p>

<p>Innovations Plus at the Sands/Venetian will feature the largest concentration of market-specific TechZones at the 2007 International CES. The Anytime-Anywhere TechArena, sponsored by Sling Media, includes content-specific areas such as The Download and the IPTV TechZone. Also, the Consumer Voice over Internet Protocol (VoIP) TechZone and other emerging technology-focused TechZones such as Consumer Robotics and Studio@Home each have dedicated exhibit area for the hottest companies and products in these respective markets. Other innovative TechZones at Innovations Plus include Bluetooth, Digital Living Network Alliance (DLNA), ExpressCard, and Mobile and Personal Broadband. </p>

<p>Returning to the Sands/Venetian this year is the Innovations 2007 Design and Engineering Award honoree display, which showcases the best-designed and engineered products of the year. New this year, attendees can use touch screen voting kiosks to vote for the Innovations People's Choice award, representing CES attendees' favorite Innovations honoree product. The Innovations showcase also features a stage for live presentations and product demonstrations from honorees. </p>

<p>CES Unveiled: The Official Press Event of the International CES takes place 4-7 p.m. Saturday, January 6 in the Marco Polo Ballroom of the Sands/Venetian. Credentialed CES press get a sneak peek of the hottest 2007 product debuts, while checking out the Innovations Design and Engineering Honorees showcase and networking with 80 leading technology companies at the only on-site, pre-CES press preview. </p>

<p>With all the action taking place at the Sands/Venetian, CES has taken measures to ensure all CES attendees have an opportunity to visit the Sands/Venetian each day of the show. In addition to opening at 8 a.m., an hour earlier than the other venues, two separate shuttle loops will run continuously during show hours to the venue - one between the Sands/Venetian and the Las Vegas Convention Center and one between the Sands/Venetian and all official CES hotels. </p>

<p>For credentialed press attending the exhibitor press conferences on CES Press Day, Sunday, January 7, shuttles will run from 8 a.m. - 6 p.m. every 30 minutes between the LVCC South Hall 1, Paradise Road entrance and the Sands Expo Bus Loading Area. </p>

<p>Credentialed CES press also will find a full-service press room in the lower level lobby at the Sands/Venetian in room 105. Amenities include press registration, CES bag pick-up, computers, fax machines and phones, high-speed Internet, exhibitor press kits and box lunches (with press lunch coupon) 11:30 a.m.-1:30 p.m. Monday through Thursday or while they last. </p>

<p>For more information on the 2007 International CES, including online registration, updated CES news, events and schedules, visit www.CESweb.org. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>October 17, 2006 10:34 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 460
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
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
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
 				AND entry_id <> 460
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/10/the_ces_show_approaches_--_less_hotel_space_and_higher_prices.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
