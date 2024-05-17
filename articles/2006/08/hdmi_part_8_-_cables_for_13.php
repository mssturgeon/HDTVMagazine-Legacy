<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 422";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 422 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hdmi cable, hdmi licensing, equalizer technology, test mhz, cable quality, cable, hdmi, HDMI, cables, MHz, quality, mhz, system, people, better, could, pair, test, those, should, consumers, tested, equipment, category, support" />
	<meta name="description" content="Now this is a sensitive area, isn't it?

The purpose of this article is NOT to justify or reject the concept of spending a dollar more for &quot;claimed&quot; cable quality while other people rather want to save that dollar any time the term cable is mentioned.

People take all kinds of corners on this matter, and many confrontations still happen with or without blind tests, with or without factual data." />
	<title>HDTV Magazine Articles - HDMI Part 8 - Cables for 1.3</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdmi_part_8_-_cables_for_13';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDMI Part 8 - Cables for 1.3'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_8_-_cables_for_13.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDMI Part 8 - Cables for 1.3</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>August 29, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_8_-_cables_for_13.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_8_-_cables_for_13.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_8_-_cables_for_13.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_8_-_cables_for_13.php&amp;phase=2&amp;title=HDMI%20Part%208%20-%20Cables%20for%201.3&amp;bodytext=Now%20this%20is%20a%20sensitive%20area%2C%20isn%27t%20it%3F%0A%0AThe%20purpose%20of%20this%20article%20is%20NOT%20to%20justify%20or%20reject%20the%20concept%20of%20spending%20a%20dollar%20more%20for%20%22claimed%22%20cable%20quality%20while%20other%20people%20rather%20want%20to%20save%20that%20dollar%20any%20time%20the%20term%20cable%20is%20mentioned.%0A%0APeople%20take%20all%20kinds%20of%20corners%20on%20this%20matter%2C%20and%20many%20confrontations%20still%20happen%20with%20or%20without%20blind%20tests%2C%20with%20or%20without%20factual%20data.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Before we get into Cables for 1.3 and 1080p let me bring up some issues about cables in general.</p>

<p><br />
<h2>Spend more on Cables?</h2></p>

<p>Now this is a sensitive area, isn't it?</p>

<p>The purpose of this article is NOT to justify or reject the concept of spending a dollar more for "claimed" cable quality while other people rather want to save that dollar any time the term cable is mentioned.</p>

<p>People take all kinds of corners on this matter, and many confrontations still happen with or without blind tests, with or without factual data.</p>

<p>Many take the matter with common-sense, and say: if I am connecting a $39 DVD player it would not be wise to spend $200 on the claimed higher quality of "that premium" HDMI cable, but would probably consider investing on a better cable if the equipment I am connecting is an HDTV system that cost thousands of hard earned dollars.</p>

<p>Why? To avoid running the risk of compromising the overall system quality, and for many, without even "noticing" a difference. Cables are one more piece of the system, and in order to strike a balance on the overall system quality, all pieces should be carefully selected.</p>

<p><img src="/images/articles/before-hdmi.jpg" alt="Before HDMI" align="right">The problem is: "How could one determine the true value of the so called "better" cable?</p>

<p>As with many other products, there are better-constructed cables with better materials and better plugs, but this industry that offers a cable for $20 while other company sells a similar application cable for $300, has certainly created a lot of uncertainty among consumers, and when quality in a cable performance is not easy to detect those consumers become skeptical.</p>

<p>Consumers need to know which is a good return for their investment when buying a cable relative to their system. Paying more for claimed cable quality might become a waste of money passing certain point. How could you determine that point on your system?</p>

<p>Would your system have sufficient quality to easily help you determine which cable is better? Hi-end systems could show cable quality differences better. Low-end systems might never have the capability to show what a better cable can do; so why pay more for those cables if you cannot see the difference?</p>

<p>However, even when many systems out there are actually capable to show differences without being hi-end, most people are unable to determine audio or video differences in comparisons unless they are coached on what to look for, that makes the cable issue more difficult to understand for regular consumers.</p>

<p>Some people buy several cables, test, and choose one (or none). Some people rather trust their research and analysis, and hear and view how good is their cable choice mainly justified by their imagination. Some people rather trust only companies from which they have bought before, and assume they could not go wrong with those.</p>

<p>Many people buy the cheapest cable they could find in the Internet and their eyes glow when they saved $5 on free shipment and no tax, then they use that cable to connect their $5000 HDTV, and convince themselves that is perfect, you see?" No difference! Every one else is wrong!</p>

<p>Some people use the cable that came with the equipment, which generally is provided for convenience but historically could be easily improved.</p>

<p>So, you might say "thanks for letting me know about all the circus, but now just tell me what to buy, and I hope I would not have to pay more than the zip cord on my desk lamp; my cousin has done all his hi-end cables with Home Depot zip cord and he sees no difference, he is now thinking about doing an HDMI cable that way."</p>

<p>Frankly. Your call. Your pocket. Your HD system.</p>

<p>This is just a simple brainstorming of points of view to motivate fair thinking without taking positions and impose those positions to everyone else that did not follow that path. Regardless what your preferences are, consumers should respect each other's cable choices; that is what is missing on most exchanges dealing with cables, there is a lack of respect for each other's decisions, specially respect for those that can actually hear or see differences in quality and chose a quality cable due to that.</p>

<p>Now, let us see what HDMI says about cables.</p>

<p><br />
<h2>The HDMI Cable</h2></p>

<p><img src="/images/articles/after-hdmi.jpg" alt="After HDMI" align="left">HDMI Licensing said that they do not have anything in detail written up but "the single most important factor in an HDMI cable is to have low "intra-pair skew", which is a fancy way of saying that the wires in the cable should be exactly the same length."</p>

<p>And they added, "Intra-pair means that what really matters is the relative length of the two wires ("D+" and "D-") in each data pair. Reliability is not impacted by one pair being substantially longer than another pair but it is severely impacted if the two wires within a pair are substantially different."</p>

<p>Interestingly, they have seen an almost inverse relationship between cable performance (primarily impacted by intra-pair skew) and cable thickness, which unfortunately could be very roughly generalized as "the more you pay (and I add "for thickness to impress, not necessarily for the higher cost of quality") the lower the performance". The suspicion is that it has something to do with the difference in manufacturing technique for thicker-gage wires, for which it might become harder to get consistent tensioning.</p>

<p>The lesson quoted from HDMI Licensing to the cable makers is: "you have always designed for analog, but HDMI uses digital, differential signals, in Gbps. Manufacturers can't keep using the same technique that they have used in the past."</p>

<p>Another case was from a person that had paid over $200 for a premium quality HDMI wire from a famous name of cable manufacturing (you know the name) and was having problems with the 1080p inputs of a Brillian 1080p RPTV; after much troubleshooting Brillian suggested another cable, it worked. The HDMI cable was not capable for 1080p transport, even at the high price, more on cable categories below.</p>

<p><br />
<h2>Do we really need another cable for 1.3?</h2></p>

<p>The HDMI specification from its inception of revision 1.0 allows cables to be designed to support up to 165MHz speeds. With HDMI 1.3's higher speeds, cables will need to undergo new testing in order to be verified for >165MHz.</p>

<p>However, "the expectation" (as expressed by HDMI) is that any cables that pass today's HDMI test at 165MHz will pass the new test at 340MHz. This is because HDMI sinks (such as HDTVs) are required to use an equalizer technology for signal speeds above 165MHz, and this equalizer technology compensates for the signal losses when the interface is clocked all the way up to 340MHz without any modifications.</p>

<p><img src="/images/articles/hdmi-mini-connector.jpg" alt="HDMI &amp; Mini Connector" align="right">There are implications in material & manufacturing methods for HDMI cables to be able to support 1080p resolutions, which is twice the data rate of 720p & 1080i. HDMI 1.3's equalizer technology provides an elegant way to allow 1080p (150MHz) compliant cables to be extended to work all the way up to 340MHz.</p>

<p>If a cable maker wants to be able to rate his cable as supporting >165MHz speeds, that cable must be re-submitted to be verified for the higher speed. However, if the cable has passed the HDMI test for 165MHz, it should pass the new HDMI 1.3 test all the way up to 340MHz. Mini-connector HDMI cable picture on the right.</p>

<p>There are very few HDMI cable makers shipping cables that have been tested at 1080p (and thus can not claim such support). The entire HDMI cable installed base is only tested & rated to support 75MHz or 720p/1080i resolutions, and receive a term now called "Category 1" for the cable. We should be seeing a new generation of HDMI cables that will come out and be labeled with a new category of being high speed capable.</p>

<p>All cables that want to claim the ability to support deep color or 1080p must be tested to pass as a "Category 2" cable. Consumers who plan to run a 1080p signal to their TVs using devices such as a PS3 or Blu-ray player should make sure they obtain a Category 2 rated HDMI cable to ensure proper compatibility. Note that all cables tested by Simplay HD are Category 2 rated, and cables which bear the Simplay HD logo are available today.</p>

<p>What happens with the equipment connected with those capable cables? According to HDMI Licensing, all the equipment that desire to take advantage of these latest features available in HDMI 1.3 would need to be revised with newer HDMI 1.3 electronics and then tested for compliance with the applicable HDMI Compliance Test Specification.</p>

<p>On one of my last conversations about the subject of cables, HDMI Licensing, responding to some of the complaints about HDMI connectors disconnecting themselves from the back panel of equipment, informally indicated that they were working in the development of a locking mechanism so the HDMI plug would stay in place, however, there was no confirmation if the effort would actually be implemented and when.</p>

<p>Now, I am ready, my fire retardant suit is on.</p>

<p>Stay tuned for Part 9 "Industry Adoption of HDMI".</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>August 29, 2006 07:39 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 422
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
 				AND entry_id <> 422
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_8_-_cables_for_13.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
