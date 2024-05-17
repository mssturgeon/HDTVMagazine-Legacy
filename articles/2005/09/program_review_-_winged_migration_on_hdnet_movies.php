<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 198";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 198 AND placement_is_primary = 1";
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
	<meta name="keywords" content="winged migration, jacques perrin, long distance, hdnet movies, birds flight, winged, migration, birds, Migration, Winged, perrin, film, most, jacques, Perrin, HDTV, fly, long, hdtv, flight, hdnet, distance, earth, time, production" />
	<meta name="description" content="&lt;em&gt;&quot;{For eighty million years, birds have ruled the skies, seas and earth. Each spring, they fly vast distances. Each Fall, they fly the same route back. This film is the result of four years following their amazing odysseys, in the northern hemisphere and then the south, species by species, flying over seas and continents.&quot;&lt;/em&gt;- Jacques Perrin (from &quot;Winged Migration&quot;)

I was heading to bed when I decided to make one last check of my HDTV channels. &quot;Wow!&quot; I heard myself exclaim, &quot;What is that?&quot;

For the next hour and something I sat transfixed and cheered by one of the most beautiful HDTV presentations I have seen since the opening ceremonies of the Winter Olympics.
" />
	<title>HDTV Magazine Articles - Program Review - Winged Migration on HDNet Movies</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/program_review_-_winged_migration_on_hdnet_movies';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Program Review - Winged Migration on HDNet Movies'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/09/program_review_-_winged_migration_on_hdnet_movies.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Program Review - Winged Migration on HDNet Movies</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>September 22, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Programming">Programming</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/09/program_review_-_winged_migration_on_hdnet_movies.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/09/program_review_-_winged_migration_on_hdnet_movies.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/09/program_review_-_winged_migration_on_hdnet_movies.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/09/program_review_-_winged_migration_on_hdnet_movies.php&amp;phase=2&amp;title=Program%20Review%20-%20Winged%20Migration%20on%20HDNet%20Movies&amp;bodytext=%3Cem%3E%22%7BFor%20eighty%20million%20years%2C%20birds%20have%20ruled%20the%20skies%2C%20seas%20and%20earth.%20Each%20spring%2C%20they%20fly%20vast%20distances.%20Each%20Fall%2C%20they%20fly%20the%20same%20route%20back.%20This%20film%20is%20the%20result%20of%20four%20years%20following%20their%20amazing%20odysseys%2C%20in%20the%20northern%20hemisphere%20and%20then%20the%20south%2C%20species%20by%20species%2C%20flying%20over%20seas%20and%20continents.%22%3C%2Fem%3E-%20Jacques%20Perrin%20%28from%20%22Winged%20Migration%22%29%0A%0AI%20was%20heading%20to%20bed%20when%20I%20decided%20to%20make%20one%20last%20check%20of%20my%20HDTV%20channels.%20%22Wow%21%22%20I%20heard%20myself%20exclaim%2C%20%22What%20is%20that%3F%22%0A%0AFor%20the%20next%20hour%20and%20something%20I%20sat%20transfixed%20and%20cheered%20by%20one%20of%20the%20most%20beautiful%20HDTV%20presentations%20I%20have%20seen%20since%20the%20opening%20ceremonies%20of%20the%20Winter%20Olympics.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img alt="programreview4.jpg" src="http://www.hdtvmagazine.com/articles/images/mt/programreview4.jpg" width="606" height="96" /></p>

<p><br />
<strong>Winged Migration</strong></p>

<p><em>"For eighty million years, birds have ruled the skies, seas and earth. Each spring, they fly vast distances. Each Fall, they fly the same route back. This film is the result of four years following their amazing odysseys, in the northern hemisphere and then the south, species by species, flying over seas and continents."</em>- Jacques Perrin (from "Winged Migration")</p>

<p>_____________________________________________________________________</p>

<p>I was heading to bed when I decided to make one last check of my HDTV channels. "Wow!" I heard myself exclaim, "What is that?"</p>

<p>For the next hour and something I sat transfixed and cheered by one of the most beautiful HDTV presentations I have seen since the opening ceremonies of the Winter Olympics. The later remains my benchmark from which all other HDTV programs are measured, but "Winged Migration" has equaled that and added a new dimension as well. You still have time to see this program on HDNet Movies at 1:15 PM ET / 10:15 AM PT - Sun, Sep 25th and 7:15 AM ET / 4:15 AM PT - Mon, Sep 26th.  </p>

<p>DON'T MISS IT. </p>

<p>Why?</p>

<p>This award winning 89 minute documentary from director, Jaques Cluzaud with narration by Jaques Perrrin, would have been clicked away had it not been for the stunning HDTV which carried it. No doubt in theaters, where it was first seen, it did captivate through imagery. I doubt it would have been of much interest on NTSC, the old standard we are leaving quickly, but with HDTV I was transported to the opposite poles of the world most all from a bird's eye view. The raw power of this presentation was given strong emphasis by the accompanying audio. If this production represents the potential of those that will regularly come from artists who love their subjects and the medium we mutually love we are in for one hell-of-a ride, perhaps even more than we deserve (but we'll take it anyway)! </p>

<p>Some comments I found on the web about this production: </p>

<p>"Long one of France's most respected producers (Academy Award Winners "Z" and "Black and White in Color") and actors ("Z," "Cinema Paradiso," "The Young Girls of Rochefort," "Donkey Skin" and "The Brotherhood of the Wolf"), Jacques Perrin has more recently had a highly successful career creating films about nature, including "Le Peuple Singe" (monkeys) and "Microcosmos" (insects) and set in exotic locales ("Himalaya"). </p>

<p>Now with his penultimate film "Winged Migration" Perrin takes on his greatest challenge yet: exploring the mystery of birds in flight. Five teams of people (more than 450 people, including 17 pilots and 14 cinematographers) were necessary to follow a variety of bird migrations through forty countries and each of the seven continents. The film covers landscapes that range from the Eiffel Tower and Monument Valley to the remote reaches of the Arctic and the Amazon. All manner of man-made machines were employed, including planes, gliders, helicopters, and balloons, and numerous innovative techniques and ingeniously designed cameras were utilized to allow the filmmakers to fly alongside, above, below and in front of their subjects. The result is a film of staggering beauty that opens one's eyes to the ineffable wonders of the natural world."</p>

<p> <img alt="albatross800.55.jpg" src="http://www.hdtvmagazine.com/articles/images/mt/albatross800.55.jpg" width="440" height="330" /></p>

<p><br />
"Winged Migration is a glorious celebration of birds in flight, conveying the beauty, the amazing feats of strength and the endurance of their long distance journeys. Here is one to stir your soul!"<br />
-- Frederic and Mary Ann Brussat,</p>

<p>"Nature films are assumed to be plotless, but Winged Migration is full of major and minor narratives, from the basic struggle of a snow goose making its migratory trek from the Gulf to the Yukon, to sequences of decidedly high drama."<br />
-- John Anderson, NEWSDAY</p>

<p>"Though you learn less about the various species Perrin circled the globe to document than you might from an afternoon with Animal Planet, you become intensely chummy with the process and labor of flying."<br />
-- Michael Atkinson, VILLAGE VOICE</p>

<p>   "A fascinating motion picture."<br />
-- James Berardinelli, REELVIEWS</p>

<p>   "Winged Migration is one for the birders, or for all other people who have stood still and forgotten themselves as they watch a sparrow make its way through the world."<br />
-- Ty Burr, BOSTON GLOBE</p>

<p>"Perrin's film assembles discontinuous but overlapping visual wonders into a vaguely mystical ode to the endless variety and timeless rhythms of life."<br />
-- Bob Campbell, NEWARK STAR-LEDGER</p>

<p>   "The movie offers ample amounts of power, poetry and even humor."<br />
-- Robert Denerstein, DENVER ROCKY MOUNTAIN NEWS</p>

<p>   "There are sights here I will not easily forget."<br />
-- Roger Ebert, CHICAGO SUN-TIMES</p>

<p>   "This is a movie to be seen and savored. And savored again."<br />
-- Eleanor Ringel Gillespie, ATLANTA JOURNAL-CONSTITUTION</p>

<p>   "There's not a single special effect, and yet the visuals are spectacular."<br />
-- Rick Groen, GLOBE AND MAIL</p>

<p>   "Provides such an intense vicarious experience of being a flapping airborne creature with the wind in its ears that you leave the theater feeling like an honorary member of another species."<br />
-- Stephen Holden, NEW YORK TIMES</p>

<p><br />
"Earthbound, watching the birds fly across the sky, we undertook this film. We had to go higher, nearer the birds, within striking distance of the stars. How could we manage it? Man has dreamt of birds since the beginning of time. How to imagine being among the first to transform this dream into reality? I will always treasure the memory of the first time we achieved this. The cameraman was following the movements of the geese, with one hand the assistant pushed away those who came too near the camera: the whole spool of film ran out. Radiant, tears in their eyes, they looked at me, speechless, motionless. Their mastery and the technical result were of minor importance, they had been in the confidence of the birds in flight. What if, for the space of a year, we no longer waited for the seasons, what if we embarked on the most fabulous of journeys, what if, abandoning our towns and our countryside, we went on a tour of the planet? What if we understood that our borders did not exist, that the earth is a one and only space and what if we learned to be free as birds?"<br />
-Jacques Perrin</p>

<p><img alt="crane800.55.jpg" src="http://www.hdtvmagazine.com/articles/images/mt/crane800.55.jpg" width="440" height="330" /></p>

<p><br />
See it on HDNet Movies </p>

<p>About the Birds<br />
"Winged Migration" is a film dedicated to birds and their displacements according to the seasons. For every one of us, these winged creatures are among the most fascinating, the most shrouded in mystery and poetry. Among all the vertebrates, they are the only ones to have mastered the open sky. Through a series of miracles of evolution, they have conquered all the skies by equipping themselves with remarkably adapted organs, wings covered with feathers, powerful muscles to move them, the heart of a long distance runner. They combine a minimum of weight with maximum strength and ease. They make up one of the most extraordinary successes of evolution, after having come from a reptilian ancestor crawling on the ground. Their flight gives them an accurate place in the biosphere; no other animal has ever come to contest this. Their exceptional faculties have allowed them to answer annual fluctuations in the climates by finding refuge during the winter far from their homelands where they breed. They are the undeniable champions among all the long distance migrants. The life of many of them is spent in long peregrinations between the place where they nest and the one where they live during the winter. Many change continents. Some fly around the earth in untiring turns. And this in spite of the risks which await them. In order to better face them, even the most solitary gather together in gigantic groups, one of the great shows of nature. To perform these exploits, as in anticipation of the efforts awaiting them, the birds accumulate reserves of fats before their departure. To guide themselves, they have discovered astronomical bearings, observing the sun and the stars. They perceive the magnetic field of the earth as the needle of a compass. They have an internal clock which gives them the time and the season of the year. The hereditary innate and a part of apprenticeship with their elders, informs them on the term of their voyages and the skyways to reach them. They know how to cope with weather conditions in an uninterrupted dialogue with the wind. "Winged Migration" relates the saga of these myriad of birds all along their migration routes.<br />
-Professor Jean Dorst, French Academy of Sciences</p>

<p>Filmmakers  -  JACQUES PERRIN<br />
Director  -  JACQUES CLUZAUD<br />
Co-Directors  -  MICHEL DEBATS<br />
Narrator  -  JACQUES PERRIN</p>

<p></p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>September 22, 2005 04:50 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 198
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
 			<h2>More on Programming</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Programming'
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
 				AND entry_id <> 198
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/09/program_review_-_winged_migration_on_hdnet_movies.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
