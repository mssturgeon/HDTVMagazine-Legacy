<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 74";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 74 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="high definition, program production, hdtv program, per second, hours per, HDTV, hdtv, production, definition, high, quality, format, television, today, program, programs, per, broadcast, cbs, CBS, film, system, electronic, digital, international" />
	<meta name="description" content="HDTV is always the best, not the second best, not the third best, and not the previous best. Today, the best is 1080/1920 at an aspect ratio of 16:9, interlace and progressively scanned. Today, this 1080 line digital wide screen, high definition format has escalated &quot;Broadcast Quality&quot; to a plateau never before imagined.
" />
	<title>HDTV Magazine Archive &amp; History - HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/high-definition_production_quo_vadis--march_2000';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/high-definition_production_quo_vadis--march_2000.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HIGH-DEFINITION PRODUCTION Quo Vadis--March 2000</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 12, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/high-definition_production_quo_vadis--march_2000.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/high-definition_production_quo_vadis--march_2000.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/high-definition_production_quo_vadis--march_2000.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/high-definition_production_quo_vadis--march_2000.php&amp;phase=2&amp;title=HIGH-DEFINITION%20PRODUCTION%20Quo%20Vadis--March%202000&amp;bodytext=HDTV%20is%20always%20the%20best%2C%20not%20the%20second%20best%2C%20not%20the%20third%20best%2C%20and%20not%20the%20previous%20best.%20Today%2C%20the%20best%20is%201080%2F1920%20at%20an%20aspect%20ratio%20of%2016%3A9%2C%20interlace%20and%20progressively%20scanned.%20Today%2C%20this%201080%20line%20digital%20wide%20screen%2C%20high%20definition%20format%20has%20escalated%20%22Broadcast%20Quality%22%20to%20a%20plateau%20never%20before%20imagined.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Another in this series of addresses from Dr. Joseph Flaherty, CBS. <br />
An Address Given to the CANADIAN SATELLITE USERS ASSOCIATION CONFERENCE<br />
TORONTO, CANADA<br />
MARCH 23, 2000 </p>

<p>HIGH-DEFINITION PRODUCTION Quo Vadis</p>

<p>Over the years, many systems have been called High Definition Television' As far back as 1935, in a report on the emergence of television, David Sarnoff, then President of RCA said:</p>

<p>"Public interest in television continues unabated since ... RCA stated that it was diligently exploring the development of television. Our laboratory efforts have been guided by the principle that the commercial application of such a service could be achieved only through a system of high-definition television."</p>

<p>Thus, in 1935 it was 343 lines, in prewar England it became 405 lines, by the 1939 New York World's Fair it was 441 lines, 525 line NTSC was introduced as a "high definition color television system", and in latter day Europe HDTV became 625 lines.</p>

<p>In short HDTV has always been, and will always be, the best quality achievable with a given state-of-the-art. HDTV is always the best, not the second best, not the third best, and not the previous best. Today, the best is 1080/1920 at an aspect ratio of 16:9, interlace and progressively scanned. Today, this 1080 line digital wide screen, high definition format has escalated "Broadcast Quality" to a plateau never before imagined.</p>

<p>The constant search for higher quality television has been endless, and there has never been a significant quality improvement in television technology that has not become a part of everyday American life. HDTV is just the latest such "must have" technology.</p>

<p>While engineers love to agonize over the relative benefits of interlace and progressive scanning and over the relative importance of spatial and temporal resolution, there is no question whatsoever that 1080/1920P is vastly superior to any other high definition system by at least a million pixels-per-frame. Moreover, 1080/1920p is with us today in 24, 25, and 30 frames-per-second with 50 and 60 frames-per-second about two years away. In fact, as you already know, all programs produced on film are always broadcast in the progressive format today.</p>

<p>Anyone in this room that believes, or wants to believe, that the public won't ever want wide screen digital HDTV when it's offered are taking a "bet-the-business" gamble.</p>

<p>Beginning last year, the new U.S. program season saw the massive launch of digital HDTV programming throughout the country. This, coupled with the rapid rollout of DTV stations that now cover over 58% of television homes, able to reach over 100 million people, forms a solid base for the DTV/HDTV transition in America.</p>

<p>The latest report from the FCC shows that:</p>

<p>• 410 stations have applied for a permit to construct digital television facilities.<br />
• 306 stations have been granted permits,<br />
• 119 stations are already broadcasting DTV and HDTV,<br />
* 25 television markets have two or more DTV/HDTV stations on-the-air,</p>

<p>In markets, ranked lower than 30, the digital transition continues with 21 stations now on-the-air with more to come.</p>

<p>As the digital transition takes place, there will be a host of transmission formats available to broadcasters, DTH and cable operators from the inferior VHS format through 480 I&P, 720P, to full 1080 I&P HDTV. While transmission systems have severe bandwidth restrictions that limit the ultimate quality today, HDTV program production has far more bandwidth flexible and important program production requires the highest possible quality. Thus, the production and distribution of programs is a wholly different matter from the multiple transmission and delivery systems employed by the multiple distribution media in important prime HDTV production it is critical to capture, record, post produce, and finish productions in the highest possible quality to protect the finished product quality and to protect the archive value for future broadcast and for both domestic and international syndication sales. Programs may be downconverted to all the lesser transmission formats, but they must be mastered in full HDTV to remain competitive in the domestic and world program markets.</p>

<p>As to the all-important HDTV programs, last September CBS began transmitting 14 1/2 hours-per-week of prime time programming in 1080 I&P high definition along with "specials", movies, and major sporting events, including I 8 hours of the US Open Tennis Championship at Forest Hills.</p>

<p>Today, CBS broadcasts 15 hours-per-week of prime time programming, covering 17 individual programs and CBS will broadcast the NCAA Final Four" basketball championships in 1080 HDTV on April I to 3, and 10 1/2 hours of the Master's golf tournament April 6 to 9, live from Augusta, Georgia.</p>

<p>Most importantly all of these high definition programs are sponsored and paid for by advertisers!</p>

<p>NBC, HBO, Madison Square Garden, Warner Bros-, DirecTV, The Discovery channel, and Capitol Broadcasting are transmitting over 120 hours-per-week in the 1080 I&P HDTV format. Of this amount of HD programming, Madison Square Garden has produced and distributed 40 hours-per-month of world class basketball and hockey this season, and another 5 to 20 hours of 1080 high definition will be added to their schedule each month with the broadcast of major league baseball games this summer.</p>

<p>Today, the 1080 I&P format is rapidly becoming the unarguable HDTV program production format worldwide, and it is the only HDTV program production and international exchange standard approved by the International Telecommunications Union in its ITU-R Recommendation BT-709-3. This ITU Recommendation is based on the "Common Image Format" or CEF of 1920 samples-per-line at an aspect ratio of 16:9 with 1080 lines-per-progressively scanned at 24, 25, and 30 frames-per-second and both interlace, and progressively scanned at 50, and 60 pictures-per-second. The 1080 line production important as- it-makes-possible the electronic production of film style programs for both TV and for the cinema. Heretofore, electronic production did not meet the quality requirements of 35mm film production, and the 1920/108OP/24-frame Common Image Format has erased this quality limitation for the electronic production of film programs. In fact, 80% of the recent Star-Wars production "Episode I - The Phantom Menace' was produced electronically with the electronic camera photography done using the 1920/1080 CIF format. The Lucas organization has now announced that much of the next Star Wars production of "Episode 11 will be produced electronically with the camera photography using the 1920/108OP/24-film CIF format. With the 1920/1080124 I&P format available, why would anyone produce electronic film any other way?</p>

<p>In addition to CBS, NBC, HBO, Madison Square Garden, Warner Bros., PBS, DirecTV, The Discovery Channel, and Capitol Broadcasting using the CIF 1920/1080 HDTV format, the Asian-Pacific Broadcast Union (ABU) has adopted the 1080 CIF format as its unique HDTV program production and international exchange standard for use throughout the Asia-Pacific region.</p>

<p>Thus, after 50 years of a TV technological "Tower of Babel", the world has seen the emergence of a single worldwide HDTV program production and international exchange standard.</p>

<p>There is finally a way to move and sell HD programs and sporting events around the world in a single image format, and the world's major programmers are adopting it.</p>

<p>As high definition program schedules expand in broadcasting, in DTH and cable systems, it is important to remember that in the competition for viewers, wide screen, high definition programs will be just a "channel click" away from all lesser quality offerings, including those broadcast on both sides of the US/Canadian border. The competition will be horrific!</p>

<p>Lest the need for full HDTV in the prime program production is questioned, Hollywood has already set the pace worldwide. 80% of U.S. primetime television product is, and has been, produced in HD for over 40 years, namely 35mm film. Hollywood TV product dominates the world program market, its market share is growing, and its product is high definition. Today, much of this 35mm film product is being converted to the 1080 CIF HDTV format and broadcast as 1080P.</p>

<p>With, its high definition TV product, the growth in total revenues returned to the major U.S. production studios from 1987 through 1997 has increased over three times to a total of US $32 billion annually. While revenues from the theatrical distribution of movies have increased modestly, the revenues derived from the electronic distribution media of cable, DTH, home video, and television broadcasting, have increased 350 percent.</p>

<p>The demand for programs is a worldwide phenomenon, and today, some 40 percent of the total U.S. studio revenues are derived from the export of programs, and these exports, with the electronic media providing most of the growth, continue to increase at an annual rate of 17 percent.</p>

<p>Programmers who wish to maintain and increase their share of the domestic and international program markets will be forced to produce in HDTV, and the worldwide 1080 HD Common Image Format will dominate high definition program production and exchange in all the TV markets.</p>

<p>As you plan your way into HDTV and into the landscape of 21st century television, it is vital to understand that 1080 I&P, wide screen, high definition is not just pretty pictures for today's small screen TV sets. Rather, it is a wholly new digital platform that will support the larger and vastly improved displays already in development for near term commercialization.</p>

<p>However, viewing HDTV on present high definition displays is a bit like Mark Twain's comment that "Wagner's music is better than it sounds". Today HDTV is better than it looks! The display devices are the limiting quality factor, the low pass filter as it were. As of now, no display has achieved the full quality potential of the 1920/1080 CIF HDTV system. In making HD system decisions, beware of today's high definition system demonstrations Usually, the viewer is testing the limited display devices and not the HDTV systems themselves.</p>

<p>Yet this development is as it should be! The full potential of any new standard should never be fully encompassed by the existing state-of-the-art, nor should it be so futuristic as to not have its potential achievable in a foreseeable time. 'The 1080 CIF HDTV standard is beyond the present quality of displays, but not beyond the scope of rapid display development. Displays are getting better and cheaper - not poorer and more expensive!</p>

<p>Be forewarned! Full quality displays will rapidly improve and will continue to widen the quality gap between real 1080 HDTV and all lesser formats. Interim "good enough" system decisions are a "pay me now and pay me later" investment!</p>

<p>Finally, as you evaluate tomorrow's TV and HDTV and plan for its implementation, bear in mind that today's standard of service enjoyed by the viewer will not be his level of expectation tomorrow. Good enough is no longer perfect, and may become wholly unsatisfactory. Quality is a moving target, both in programs and in technology. Judgments as to future changes must not be based on today's performance or on minor improvements thereto.</p>

<p>Change is irresistible, as Victor Hugo noted when he wrote:</p>

<p>"An invasion of armies can be resisted; but not an idea whose time has come."</p>

<p>________________________________________________<br />
About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em><br />
 <br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 12, 2005 11:28 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 74
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 74
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
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/high-definition_production_quo_vadis--march_2000.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
