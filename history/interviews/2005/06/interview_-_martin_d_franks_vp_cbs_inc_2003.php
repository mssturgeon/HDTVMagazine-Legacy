<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 128";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 128 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (4) {
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
	<meta name="keywords" content="doing nfl, copy protection, game week, production equipment, cable compatibility, our, going, transition, marketplace, nfl, HDTV, think, time, NFL, hdtv, people, last, doing, been, first, college, challenge, problem, equipment, good" />
	<meta name="description" content="Martin Franks is in the profession he loves--television. He has the responsibility at CSB for managing the H/DTV transition. At the launch of the new television season we wanted to ask him to size up the progress made from last..." />
	<title>HDTV Magazine Interviews - INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_martin_d_franks_vp_cbs_inc_2003';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_martin_d_franks_vp_cbs_inc_2003.php";
		if ($author[img] != '' && 4 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">INTERVIEW - Martin D. Franks, VP, CBS, Inc. 2003</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_martin_d_franks_vp_cbs_inc_2003.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_martin_d_franks_vp_cbs_inc_2003.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_martin_d_franks_vp_cbs_inc_2003.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_martin_d_franks_vp_cbs_inc_2003.php&amp;phase=2&amp;title=INTERVIEW%20-%20Martin%20D.%20Franks%2C%20VP%2C%20CBS%2C%20Inc.%202003&amp;bodytext=Martin%20Franks%20is%20in%20the%20profession%20he%20loves--television.%20He%20has%20the%20responsibility%20at%20CSB%20for%20managing%20the%20H%2FDTV%20transition.%20At%20the%20launch%20of%20the%20new%20television%20season%20we%20wanted%20to%20ask%20him%20to%20size%20up%20the%20progress%20made%20from%20last...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>Martin Franks is in the profession he loves--television. He has the responsibility at CSB for managing the H/DTV transition. At the launch of the new television season we wanted to ask him to size up the progress made from last year to this and to see what still needs to be done to spur on the horses.</em>  </p>

<p><strong>My first question:</strong> <br />
<strong>Marty, we are in a new season. What is the first thing that comes to  mind when you think of this new season?</strong></p>

<p> It's a competitive thought. In the last several years it was nice when we didn't have much competition in HD. But as a company who cares a lot about the success of the transition I must say that it is also nice to read my HDTV Magazine in the morning and see that there is so much on in HD.</p>

<p> <strong>It is certainly growing.</strong></p>

<p>It is striking! I saw a note on one of the Internet forums talking about the fact that at one point there were going to be five different HDTV programs on at the same time. I think that is good for the business; it is good for the transition; and I think it is showing up out in the stores, where, by all reports that we receive, people are continuing to buy the product.<br />
 <br />
<strong>Do you think that the charge leveled against broadcasting for several years that there was not enough compelling programming is now fully satisfied?</strong> </p>

<p>No, it is not fully satisfied. It is dramatically better. It helped enormously when ABC came on board. I support Alex Wallau and his colleagues for joining us. It has helped to have NBC come along, particularly with their last minute edition of ER. As a fan of West Wing I keep waiting for them to do the same there… but…our biggest challenge remains to figure out a way to do more of our sports in HD. Just as prime time was the first frontier where we learned a great deal about how to do HD, the next big breakthrough is going to be when we can "regularize" more of our sports in HD. Unfortunately that is financial challenge and a combination of logistical and technological challenge.</p>

<p><strong>How would you characterize those technological challenges, and are they being met? </strong></p>

<p>Having the core digital truck that we use for the football has been a tremendous help. It save a lot of money and improved the quality of the production at the same time. But we need four or five more of those trucks to be available to use from our vendors. From the standpoint of a truck vendor it is hard to commit the capital to that kind of enterprise without a greater assurance that it is going to be used for more than the 20 Saturdays in the fall, but rather used 52 weeks a year.<br />
 <br />
That is one challenge. There are likely two other things which I glean from reading your publication and the AVS forum, which I do regularly, I think a lot of viewers are understandably impatient, but they fail to understand that there is still a ways to go in developing the HDTV production equipment. It is only this fall with our college football that we finally have a HDTV super-slow motion that we are comfortable in using. We still don’t have a first down line that we can project. We want to make sure that our HDTV broadcast are what viewers have come to expect fram  CBS Sports production.   Some of the equipment just doesn't exist. </p>

<p>The  other is the logistical challenge All of these stadiums, including ones that have been built in the last couple of years, were built with coaxial cables. When we are doing an NFL game in a modern stadium there is a plug in the wall at a camera position. They can plug the camera into the coax and there is a drop location in the basement where we can put our truck, and we are on the air.</p>

<p>Now, with very few exceptions, we have to run fiber cables each time we are in a stadium. That is expensive and time consuming. I am in hopes that as new stadiums are built they will do them with fiber. </p>

<p>It would be great to have an NFL game of the week on, and we have hopes of being able to do that in the not-too-distant future, But, I guess what I am talking about in terms of really driving this transition home is not when we are doing one NFL game each week, but that we are doing all of the NFL games. We are not going to completely succeed with this transition until the Jet’s fan who lives in New York gets his Jets game every weekend in HD, not just occasionally have an opportunity to see them when they happen to be the Game of The Week.</p>

<p> <strong>Is the announcement from ESPN going to provide some of the solutions? </strong></p>

<p>I hope. I have not talked to my friends there since their announcement. So far its been mostly ourselves and Mark Cuban doing the stimulation of the production equipment marketplace. So, having ABC come aboard will help. We have long term contracts with truck vendors so even if ESPN's vendors build a couple more trucks they won't necessarily be available to us. It will clearly help, though. Maybe there will be three or four people who want the first down line in HD instead of one the marketplace will be stimulated to produce it more quickly.</p>

<p><strong>Are you being asked to do things by manufacturers or are you asking them?</strong></p>

<p>A little of both. We still spend an enormous amount of time and resource cooperating with manufacturers on testing their equipment and helping to develop their equipment. Our engineers travel extensively to lend their expertise to this process. We do see that as a collaborative effort. When I refer to CBS's leadership it is also the technological developments which we stimulate, starting with Joe Flaherty and Bob Ross, and Bob Siedel, and am very proud of what CBS puts in behind the camera.</p>

<p><strong>At the recent hearings it was said in the opening remarks that the transition is not going as rapidly as many would like it to go? Where is your perception in respect to that statement?</strong> </p>

<p>I think we have made enormous progress in the last year. I testified at the same hearing a year and one half year ago. I said then that the government had to make up its mind. The original transition, while there was the 2006 deadline, was set to be a marketplace driven transition. I said then that the marketplace will sort all of this out, it is just not going to do it by 2006. Now, as we get closer to 2006 and the government has determined that it wants to keep as close to that date as it can it is more appropriate to push the marketplace.</p>

<p>In terms of surprises--I thought that with Rep Tauzin (R-LA) and the Chairman of the FCC Powell round tables--the jaw boning process--was making great progress on copy protection and the cable compatibility issues. I am extremely disappointed that that progress seems to have stalled once again. I know less about the cable compatibility because a) we are not in the cable business, and b) we don't manufacture sets. On the other hand we do like our viewers to have happy and easy viewing experience. </p>

<p>I know a great deal more about the copy protection issue and that is a growing problem for us. Because we are not going to allow our business to be "Napsterized". Again, I am surprised when I read on the web pages and forums people's violent reaction when we are seeking to protect our copyright.<br />
 <br />
<strong>We have been saying that either the problem is not as great as what has been said about it, or it is also a responsibility of the marketplace to not let it be a big problem. In other words, the consumer has a responsibility to you as well as you have some obligations to the consumer.</strong></p>

<p>When we did our deal with Echo star to put our HDTV feeds up, CBS gave a blanket waiver in all of the markets that we own. We thought that was a pretty good faith gesture. But it is pretty discouraging to go and read on the Internet forums people talking about how to steal that signal. <em><u>If you take signal theft to its ultimate extension then there is no incentive to create programming.</u></em> This is one of the impediments to doing the NFL. We have a contractual obligation to the NFL to maintain regions. If those regions can be defeated, guess what? We are not going to get to do the NFL in HDTV.</p>

<p><strong>We have been saying that the public is screwing themselves by doing this.</strong></p>

<p>We know we have an obligation to provide a product, and that is our part of the deal. Tthe broadcast flag was such an elegant solution to me because all it was intended to do was to keep people from pirating product over the Internet. <u><em>It was not meant to even remotely defeat copying at home or even copying on a home network.</em></u> It struck me as a good solution. I am troubled to see it stalled once again and at some point there will be consequences. I tried to get the HD version of a movie recently. The movie will air on the analog network but I could not negotiate an HD version and I could not get it. </p>

<p>I was hard pressed to tell that studio they were making the wrong judgment. Again, go to the message boards. There are people recording those movies and all of a sudden they have a perfect HD digital master of a copyrighted product and if they choose to engage in piracy they have the raw material with which to work. </p>

<p><strong>Isn't this a problem incredibly exacerbated by the statement of every engineer say that everything can be broken. You have no permanent solution, but rather a series of solutions like computer security patches, which seem to download endlessly?</strong> </p>

<p>You can never defeat piracy. We have learned the hard way from DVDs in China. You can, however, make it harder. Sure, eventually some kid in a garage in Cupertino is going to hack the algorithm. But if you look at Nepster, which about hacking the algorithm as much as it became an "in thing" to do on college campuses. Where are the colleges in this whole exercise? The notion that the colleges are allowing their servers and T1 lines to be witting accomplices in piracy is mind boggling. As one who is paying a rather substantial college tuition for a child at the moment it is not exactly a lesson I want him to be learning from his college education.<br />
 <br />
There is a solution to this problem. It will NOT inhibit home recording, home networking by anyone. All of a sudden some of the CE manufacturers have taken this pure position--they want to sell DVD recorders, etc., and I think it is short sided on their part. <br />
So, I am more worried about these issues related to the transition--cable compatibility and copy protection.--and the fact that I thought we were quite close on both scores several months ago. But we seem less close, and all of a sudden there is more programming.   If viewers continue to want shows , like CSI and CSI Miami, then their producers have to have an incentive to make that investment. Part of their incentive is the back-end market--the syndication marketplace. If that marketplace can be eroded via internet piracy, it is not a good thing.</p>

<p><strong>The quality of the programming could only decline.</strong> </p>

<p>Also the picture quality of it. We can meet our obligation to the law by broadcasting a 4:3 480 picture. What a travesty that would be. I like my CSI Miami in 16:9 and 1080i.</p>

<p>But we shouldn't let that cloud things. We have made tremendous progress in a relatively short period of time. As we were first discussing, there are going to be two college footballs games on tomorrow! (speaking of Saturday, Oct 12, 2002). Last night at ten o’clock you could watch Without A Trace or you could watch ER.<br />
 </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 12:26 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 128
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
			
 		<?if (4 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 4
 				AND entry_id <> 128
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
 				<h2>About Interviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/interviews/2005/06/interview_-_martin_d_franks_vp_cbs_inc_2003.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
