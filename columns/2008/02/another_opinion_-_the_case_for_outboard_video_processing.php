<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1240";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Terry Paullin'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1240 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (10) {
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
	<meta name="keywords" content="video processing, video artifacts, outboard box, fixed pixel, big screen, video, processing, screen, line, may, big, artifacts, could, display, outboard, resolution, front, good, even, panel, Big, set, been, box, well" />
	<meta name="description" content="If you haven't already, marketing types everywhere are saying you probably will - buy a large screen TV in 2008 that is. For our purpose here, let's define large as anything north of a 50&quot; diagonal. With the resurgence of a couple good RPTVs, precipitous price drops in both LCD and Plasma large panels and really good front projector/screen combos in the under $5,000 range, many more of you WILL opt for Big Screen nirvana this year. But will all truly be well in Megapixelville? If all you do is replace the monitor, maybe not so much.

Here's the thing. From the beginning..." />
	<title>HDTV Magazine Columns - Another Opinion - The Case for Outboard Video Processing</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/another_opinion_-_the_case_for_outboard_video_processing';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Another Opinion - The Case for Outboard Video Processing'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/columns/2008/02/another_opinion_-_the_case_for_outboard_video_processing.php";
		if ($author[img] != '' && 10 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Terry Paullin" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Another Opinion - The Case for Outboard Video Processing</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Terry Paullin</b><br />
				<?=$author_title?>
				Posted on <b>February  4, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/02/another_opinion_-_the_case_for_outboard_video_processing.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/columns/2008/02/another_opinion_-_the_case_for_outboard_video_processing.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/columns/2008/02/another_opinion_-_the_case_for_outboard_video_processing.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/02/another_opinion_-_the_case_for_outboard_video_processing.php&amp;phase=2&amp;title=Another%20Opinion%20-%20The%20Case%20for%20Outboard%20Video%20Processing&amp;bodytext=If%20you%20haven%27t%20already%2C%20marketing%20types%20everywhere%20are%20saying%20you%20probably%20will%20-%20buy%20a%20large%20screen%20TV%20in%202008%20that%20is.%20For%20our%20purpose%20here%2C%20let%27s%20define%20large%20as%20anything%20north%20of%20a%2050%22%20diagonal.%20With%20the%20resurgence%20of%20a%20couple%20good%20RPTVs%2C%20precipitous%20price%20drops%20in%20both%20LCD%20and%20Plasma%20large%20panels%20and%20really%20good%20front%20projector%2Fscreen%20combos%20in%20the%20under%20%245%2C000%20range%2C%20many%20more%20of%20you%20WILL%20opt%20for%20Big%20Screen%20nirvana%20this%20year.%20But%20will%20all%20truly%20be%20well%20in%20Megapixelville%3F%20If%20all%20you%20do%20is%20replace%20the%20monitor%2C%20maybe%20not%20so%20much.%0A%0AHere%27s%20the%20thing.%20From%20the%20beginning...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>If you haven't already, marketing types everywhere are saying you probably will - buy a large screen TV in 2008 that is. For our purpose here, let's define large as anything north of a 50" diagonal. With the resurgence of a couple good RPTVs, precipitous price drops in both LCD and Plasma large panels and really good front projector/screen combos in the under $5,000 range, many more of you WILL opt for Big Screen nirvana this year. But will all truly be well in Megapixelville? If all you do is replace the monitor, maybe not so much.</p>

<p>Here's the thing. From the beginning, when Henry Kloss plugged the audio output of his 6ft. parabolic screen TV (Novabeam One) into his stereo system, people have wanted Big screen, Big sound. Somewhere in the late eighties we started calling it Home Theater. Initially reserved for only the rich and famous, a "decent" Home Theater featured a box on the ceiling housing 3 nine-inch CRTs and weighing in at just a tick this side of a Chrysler LeBaron. The only thing harder to find than someone who could afford one, was someone who was qualified to install one. If there is any gray hair in this readership, you may fondly recall the two-day install process and the 128-point convergence grid that you had to revisit every six months or so. The old behemoths made pretty pictures when they were "right", but all things considered from an installer's perspective, may CRT technology rest in peace - forever!</p>

<p>Today we live in a nearly all digital, fixed pixel world. We can worry about mis-aligned LCD panels and chromatic aberration in the lens, but the days of spending hours of trying to get red, green and blue to line up are over. For front OR rear projection, the boxes are smaller and lighter as well. Alas, we have new demons rearing their heads due to XL screen sizes.</p>

<p>It may be a bit ironic that the very thing that gets people to open up their wallets nowadays (BIG SCREENS) brings with it a new nemesis - one that's been with us all along but now makes its presence felt logarithmically with screen size - video artifacts.</p>

<p>Video artifacts are anything in the displayed picture that was not there in the source camera. The abolition of video artifacts was the motivation for founding the Imaging Science Foundation, the reason that Ives Faroudja was able to sell his company for 40 million dollars, and the reason that a significant number of Big Screen TVs are returned every day. Turns out, the antidote for many video artifacts is precision video processing.</p>

<p>To be clear, there are many flavors of video processing. You are probably most familiar with de-interlacing and scaling, but there are several kinds of each of these (i.e., motion, edge and source adaptive de-interlacing, for example) and other kinds of processing such as gamma correction, cadence detection and matching and several types of noise reduction, all aimed at artifact reduction or elimination. The purpose here is not to make you an expert on all manner of video processing, but rather to suggest you may be better off adding a greater degree of video processing than was built into the $1,500 LCD panel you hung last week. This could be accomplished by selecting more expensive display monitors with commensurately better on board processing, but is often easier and more effectively provided by the installation of an outboard box designed for just such a purpose.</p>

<p>But let's back up a second and review some basics. If the aforementioned panel has a native resolution of, say, 1366 X 768 (common) that is the ONLY resolution you will see, because that's all the panel is capable of displaying - not one line less, not one line more. Since the display manufacture had no idea what kind of sources (or which of their settings) you were going to throw at it, they had no choice but to put some kind of scaling processor on the front end of the video input, scaling ALL incoming signals to a vertical resolution of 768 and a horizontal resolution of 1366. Further, no fixed pixel display device can display an interlaced signal. We only got away with that with CRTs because we could count on the combined persistence of our eyes and the phosphors in the tube to "trick" us into thinking "fast flickering" 240-line images actually looked like relatively smooth 480-line pictures. Net, net, every fixed pixel display device must have some form of de-interlacer built in (Note: "line doubling" has always been an incorrect term for de-interlacing).</p>

<p>In this ultra competitive world of consumer electronics, we pretty much get what we pay for. So what sort of video processing "chip set" do you suppose comes with that Acme HD-1 panel down at Costco for $799? It's unlikely that the whole processing chipset plus the amortized engineering that went into it, cost more than the foam and cardboard packaging that protected the screen.</p>

<p>Now comes 60, 61, 63, 65, 67, 70 and 72 inch diagonal screens on a regular basis. Video artifacts that would never be seen on a 27" Magnavox will now scream at you from the 73" Mitsubishi, not to mention the 10ft. front projector screen your wife talked you out of! If you have already made this size leap, you may have said to yourself - "Boy, that HD looks terrific, but I think SpeedVision and the food channel look worse". If I were your installer and you asked me what's up with that, I could launch into a treatise on how all content providers would rather MPEG compress "QVC" to a bit-rate of 50 Mbps per fortnight in favor of giving you more pay-per-view channels, or calmly say "Just wait a year or so when all broadcasts will be in HD anyway", or, do what I knew I should have done, and step up to installing some form of (good) outboard video processing.</p>

<p>Going to an outboard box for video processing can have several side benefits as well. They are terrific switchers. Unlike even some expensive receivers, most have 3 or 4 HDMI inputs and most all will transcode - meaning they will take "S" video in, for example, and send it out in digital format. Most all switch audio as well and some even have lip sync time delay capability. So while the best processors may carry a two or three thousand dollar premium, they could save much of that back in the sophistication otherwise required of the receiver or pre/pro. I happen to like DVDO products because in addition to all the features described above, their model VP50-Pro also has the unique ability to re(de-interlace). That means they will accept progressively scanned video, take it apart, and de-interlace it properly with their more effective algorithms. As some set-top boxes can only send out a progressive signal, this is a real plus. Best of all, three manufactures I know of (DVDO, Lumagen and Key Digital) have ISFccc modes, which means settings for day and night viewing can be locked after the set has been calibrated.</p>

<p>HDTV has changed our standards. Indeed, it will make you more critical of lower resolution images, even if they were on the same size screen that you've been used to. If your favorite programming still includes a steady diet of 480 line video, I suggest you try an outboard box before investing in a big new receiver/pre-pro just to get more HDMI inputs. Even if you are fast becoming an "HD only" person, the dedicated, high quality video processing will make a welcomed visible difference. Don't be afraid of the set-up. There are lots of menu choices on these boxes, but that's a good thing. Basically, it ensures they will work in any situation and on YOUR video monitor. People like DVDO (and others, I'm sure) have excellent tutorials and phone support - they don't like returns either.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Terry Paullin</b>, <b>February  4, 2008 09:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1240
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
			
 		<?if (10 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 10
 				AND entry_id <> 1240
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Terry Paullin'
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
 				<h2>About Terry Paullin</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Columns</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/02/another_opinion_-_the_case_for_outboard_video_processing.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
