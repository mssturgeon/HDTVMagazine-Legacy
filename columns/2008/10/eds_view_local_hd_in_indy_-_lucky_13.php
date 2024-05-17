<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1505";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1505 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hdtv programming, bas transition, presented digital, spectrum space, bas spectrum, HDTV, hdtv, local, wthr, WTHR, digital, programming, transition, bas, network, BAS, been, broadcasting, business, time, indeed, new, ATSC, atsc, broadcast" />
	<meta name="description" content="Actually, it's Indianapolis that's lucky to have WTHR, Channel 13, as one of the premier US leaders in providing local HDTV programming. WTHR is at this time the only Indiana station producing HDTV local news and other local programming in this 25th largest market in the US - in which it has been consistently Number 1. This narration is not to be considered a &quot;commercial&quot; for WTHR, (but it would not be judged an affront if so assumed). Rather it is a salute to all those local digital broadcasting pioneers who have had the foresight to fully embraced the technology and adjust their business plans to exploit all the benefits to their customers that can be provided - including, most specifically, locally produced HDTV programming. 

It is no secret that the old over-the-air (OTA) TV broadcast business model is in trouble at both the major network and local levels..." />
	<title>HDTV Magazine Columns - Ed's View: Local HD in Indy - Lucky "13"</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_local_hd_in_indy_-_lucky_13';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View: Local HD in Indy - Lucky "13"'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/columns/2008/10/eds_view_local_hd_in_indy_-_lucky_13.php";
		if ($author[img] != '' && 10 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View: Local HD in Indy - Lucky "13"</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>October  7, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Broadcast">Broadcast</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/10/eds_view_local_hd_in_indy_-_lucky_13.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/columns/2008/10/eds_view_local_hd_in_indy_-_lucky_13.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/columns/2008/10/eds_view_local_hd_in_indy_-_lucky_13.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/10/eds_view_local_hd_in_indy_-_lucky_13.php&amp;phase=2&amp;title=Ed%27s%20View%3A%20Local%20HD%20in%20Indy%20-%20Lucky%20%2213%22&amp;bodytext=Actually%2C%20it%27s%20Indianapolis%20that%27s%20lucky%20to%20have%20WTHR%2C%20Channel%2013%2C%20as%20one%20of%20the%20premier%20US%20leaders%20in%20providing%20local%20HDTV%20programming.%20WTHR%20is%20at%20this%20time%20the%20only%20Indiana%20station%20producing%20HDTV%20local%20news%20and%20other%20local%20programming%20in%20this%2025th%20largest%20market%20in%20the%20US%20-%20in%20which%20it%20has%20been%20consistently%20Number%201.%20This%20narration%20is%20not%20to%20be%20considered%20a%20%22commercial%22%20for%20WTHR%2C%20%28but%20it%20would%20not%20be%20judged%20an%20affront%20if%20so%20assumed%29.%20Rather%20it%20is%20a%20salute%20to%20all%20those%20local%20digital%20broadcasting%20pioneers%20who%20have%20had%20the%20foresight%20to%20fully%20embraced%20the%20technology%20and%20adjust%20their%20business%20plans%20to%20exploit%20all%20the%20benefits%20to%20their%20customers%20that%20can%20be%20provided%20-%20including%2C%20most%20specifically%2C%20locally%20produced%20HDTV%20programming.%20%0A%0AIt%20is%20no%20secret%20that%20the%20old%20over-the-air%20%28OTA%29%20TV%20broadcast%20business%20model%20is%20in%20trouble%20at%20both%20the%20major%20network%20and%20local%20levels...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p align="center"><b><img style="border-top-width: 0px; border-left-width: 0px; border-bottom-width: 0px; border-right-width: 0px" height="83" alt="WTHR 13" src="http://www.hdtvmagazine.com/images/articles/EdsViewLocalHDinIndyLucky13_1197E/clip_image002.jpg" width="112" border="0"></b></p> <p>Actually, it's Indianapolis that's lucky to have WTHR, Channel 13, as one of the premier US leaders in providing local HDTV programming. WTHR is at this time the only Indiana station producing HDTV local news and other local programming in this 25th largest market in the US - in which it has been consistently Number 1. This narration is not to be considered a "commercial" for WTHR, (but it would not be judged an affront if so assumed). Rather it is a salute to all those local digital broadcasting pioneers who have had the foresight to fully embraced the technology and adjust their business plans to exploit all the benefits to their customers that can be provided - including, most specifically, locally produced HDTV programming.  <p>It is no secret that the old over-the-air (OTA) TV broadcast business model is in trouble at both the major network and local levels. And it has been struggling for some time, long before digital TV was on the horizon. The "disruptive" technology and associated business model that altered the entire broadcasting landscape was Cable. Since the advent of Cable and all the other alternate sources of TV programming generation and delivery, the percentage of eyeballs focusing on traditional network and local programming has been declining at an ever increasingly faster rate, regardless of how it is delivered. Traditional OTA broadcasting is certainly not the "cash cow" of the past.  <p>But a few broadcast pioneers like Dispatch Broadcasting Group of Columbus, Ohio, owners of both Indianapolis' WTHR and sister station, WBNS in Columbus, OH, realized the opportunities presented by digital television. They also realized their winning business objective must be to deliver the best area news, weather and local content with the highest production and technical values possible over as many delivery platforms possible. This is not to say that WTHR's NBC affiliate mission would be ignored or subordinated in any manner. Indeed, even though traditional network affiliate viewership is decreasing nationally, more viewers watch network programming in the aggregate than any other programming, including all the exclusive cable networks combined. But to survive and flourish as a media entity, local broadcasters cannot rely on network marketing "pull-through." Indeed, in many instances the reverse is occurring, with traditional networks depending on the strengths of the local broadcasters to maintain financial viability.  <p>So, with aforementioned new business objectives in mind, WTHR embarked on a multi-year, multi-million dollar digital transformation project spurred by those opportunities presented by digital television. Starting in 1997 with the installation of a "first generation" ATSC digital encoder to generate an HDTV signal from one of the very few pieces of HDTV video tape material available, the journey began. At that time there were no DTV receivers in existence as the tuner and decoding chips had not yet been developed. So the first "receiver" was a spectrum analyzer confirming that some kind of digital signal, roughly resembling that of the printed ATSC specifications, was indeed being transmitted. That pioneering WTHR DTV signal was very instrumental in the initial stages of the development of the first US commercially available HDTV receivers designed at the RCA (Thomson) labs in Indianapolis.  <p>Under the able guidance of Al Grossniklaus, WTHR's Director of Engineering and Operations, the station has successfully navigated through the myriad of digital transformation acquisition and installation phases. Here is a sample of these phases, roughly listed in chronological order but many occurring simultaneously:  <p><b>Digital RF Chain (Transmitter and Antenna); ATSC Encoder; Network Digital Acquisition; Studio/Transmitter Link; Master Control Switching; HDTV Camera Acquisition; Fiber-Optic Infrastructure; HDTV Studio Switching; Non-linear Editing; Server Network; Real-Time HDTV Graphics; HDTV Weather Graphics; HD/SD Eng Cameras; New HDTV Studio Sets; Automated Switching, etc.</b>  <p>And, all of those tasks have been accomplished while maintaining the analog (NTSC) signal infrastructure <i>in the same physical plant!</i>  <p>WTHR, in November, 2006, became the first, and remains the only, station in Indiana to produce local HDTV programming. Tasks remaining to be accomplished within the next several months include switching to full HDTV electronic news gathering (ENG), producing in 5.1 channel audio and transitioning to a complete end-to-end file-based production format. It is indeed a "work-in-progress."  <h2>The Other Transition</h2> <p>There is another digital transition taking place in the TV broadcast industry not well known by the "receiving" community. It is called the Broadcast Auxiliary Service (BAS) transition. Essentially, this is an FCC mandated shift in local broadcast microwave link frequencies to a new band higher in the 2 GHz spectrum space. The BAS shift is a rather convoluted technical and regulatory arrangement in which Sprint-Nextel is obtaining the vacated BAS spectrum in exchange for providing the broadcasters with new BAS equipment. There have been many changes and alterations in this program, but as of now, the ongoing transition is due to be completed in March, 2009, just a month after the ATSC transition in February.  <p>The significance of the 2 GHz BAS transition to WTHR and other broadcasters is that the all-digital structure and increased BAS spectrum space will greatly facilitate full HDTV ENG live production. At this time WTHR is able to take advantage is some local fiber cable infrastructure to originate HDTV in a few key Indianapolis remote locations, but full ENG live capability awaits their BAS transition.  <h2>The Future</h2> <p>As a programming service, WTHR is, as are many forward-thinking stations, expanding their offerings to as many distribution outlets as possible to serve several different customer segments. The transition to a full file-based (IP) infrastructure will greatly facilitate their ability to edit, re-purpose and customize their products for these media segments without the quality compromises associated with multiple format conversions. WTHR is also eagerly looking forward to the opportunities that may be provided by the upcoming ATSC Mobile/Handheld (M/H) standard. This technology may provide another powerful outlet for their products, enabling such services as on-demand "hyper-local" (precisely pinpointed) news and weather as well as various interactive information and entertainment programs.  <p>Indeed, it is a brave new world for the TV broadcasting industry. But will WTHR and OTA broadcasting in general survive and flourish? Don't know, but one thing is for sure: the lead dog always has the best view.  <p>Ed</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>October  7, 2008 09:48 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1505
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
 			<h2>More on Broadcast</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Broadcast'
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
 				AND entry_id <> 1505
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
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
 				<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/10/eds_view_local_hd_in_indy_-_lucky_13.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
