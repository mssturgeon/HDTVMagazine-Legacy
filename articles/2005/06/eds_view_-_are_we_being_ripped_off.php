<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 71";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 71 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hdtv quality, sub channels, being ripped, sub channel, high definition, HDTV, hdtv, quality, channels, video, being, channel, broadcast, sub, OTA, ota, screen, digital, any, cable, good, those, ATSC, motion, DTV" />
	<meta name="description" content="So,the subject has piqued your curiosity, as it should. For, as HDTV grows, we must act as &quot;policemen&quot; to stem attempts to compromise quality. To be sure there are, and will be, many different quality levels due to production variances, HDTV production equipment limitations, and the effect of the production &quot;learning curve.&quot; As production experience is gained and equipment improves, the quality will only get better. However, the question remains whether this positive production improvement curve will be, or indeed is being, compromised at the transmission end - regardless of the physical &quot;transmission&quot; mode - OTA broadcast, cable, DBS, etc.  Probably not, but again it depends. Why? 

Read all of Ed's View today...
" />
	<title>HDTV Magazine Articles - Ed's View - Are We Being Ripped Off?</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_are_we_being_ripped_off';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - Are We Being Ripped Off?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_are_we_being_ripped_off.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - Are We Being Ripped Off?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>June 11, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Marketplace">Marketplace</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_are_we_being_ripped_off.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_are_we_being_ripped_off.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_are_we_being_ripped_off.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_are_we_being_ripped_off.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20Are%20We%20Being%20Ripped%20Off%3F&amp;bodytext=So%2Cthe%20subject%20has%20piqued%20your%20curiosity%2C%20as%20it%20should.%20For%2C%20as%20HDTV%20grows%2C%20we%20must%20act%20as%20%22policemen%22%20to%20stem%20attempts%20to%20compromise%20quality.%20To%20be%20sure%20there%20are%2C%20and%20will%20be%2C%20many%20different%20quality%20levels%20due%20to%20production%20variances%2C%20HDTV%20production%20equipment%20limitations%2C%20and%20the%20effect%20of%20the%20production%20%22learning%20curve.%22%20As%20production%20experience%20is%20gained%20and%20equipment%20improves%2C%20the%20quality%20will%20only%20get%20better.%20However%2C%20the%20question%20remains%20whether%20this%20positive%20production%20improvement%20curve%20will%20be%2C%20or%20indeed%20is%20being%2C%20compromised%20at%20the%20transmission%20end%20-%20regardless%20of%20the%20physical%20%22transmission%22%20mode%20-%20OTA%20broadcast%2C%20cable%2C%20DBS%2C%20etc.%20%20Probably%20not%2C%20but%20again%20it%20depends.%20Why%3F%20%0A%0ARead%20all%20of%20Ed%27s%20View%20today...%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em><strong>Ed's view.</strong></em></p>

<p><strong>Are We Being Ripped Off?</strong></p>

<p>I have heard and read several remarks over the past few years concerning the negative impact on HDTV quality due to the number of sub-channels being broadcast on the station's digital channel. So, is our HDTV quality being compromised by all of this "multiplexing" activity?  In other words, are we being ripped off. The answer to this (in my view) is "it depends." If you accept this answer, it is not necessary to read further, and you may move on to other non-goal director behavior (pause)...</p>

<p>So, the subject <em>has</em> piqued your curiosity, as it should. For, as HDTV grows, we must act as "policemen" to stem attempts to compromise quality. To be sure there are, and will be, many different quality levels due to production variances, HDTV production equipment limitations, and the effect of the production "learning curve." As production experience is gained and equipment improves, the quality will only get better. However, the question remains whether this positive production improvement curve will be, or indeed is being, compromised at the transmission end - regardless of the physical "transmission" mode - OTA broadcast, cable, DBS, etc.  Probably not, but again it depends.  Why?</p>

<p>First of all, we must realize there is nothing in the FCC Digital TV rulings dictating that the digital OTA broadcast service must be HDTV. The rulings only prescribe the digital channel frequency assignments, transmission power levels, and the ATSC formats for OTA video services (e.g. MPEG 2 compression, 8VSB modulation etc.). The MPEG 2 encoding parameters allow for a 20Mps video channel that was determined, through many ATSC sponsored subjective visual evaluations, to be "HDTV." </p>

<p>However, broadcasters are free to use their allocated bandwidth for services other than HDTV, as long as one service is ATSC video. As we know, many are doing just that, usually transmitting an "HDTV" program alone with three or four news, weather, public events, etc. programs. Although other services than video is possible, OTA broadcasters have yet to find a successful business model for them.  That gets us back to the base question: is all this multiplexing, regardless of what it is, compromising HDTV quality. It certainly can, and in some instances it may be (again, it depends) but in actuality it will not, nor is it likely.</p>

<p>Why? The first reason is political - that means you and I and our elected representatives. From its inception, what we now know as HDTV was "sold" to the American people, and thus politically, as a TV system delivering very high quality images. Therefore, the FCC and congress have looked with a very jaundiced eye on supporting any broadcast spectrum allocation that does not pay off on the HDTV promise.  </p>

<p>The second reason is economical. In the very competitive world of commercial television production, and with the traditional broadcast networks watching their viewing share decrease with respect to cable networks, these networks are forced to respond to any competitive element. As HDTV becomes more popular, its ability to influence even a fraction of the viewing share becomes very important to the network programmers. Indeed, there is no doubt, since ABC's recent announcement that Good Morning America will be produced in HDTV this fall, that NBC will be forced to follow with Today in HDTV.</p>

<p>The third reason is technical. By approximation mathematics, still (non motion) 1080 ATSC images can be transmitted with maximum definition using only 5Mb/sec, and that assumes that every pixel has a different brightness level, which is virtually never the case. Anyway, the last I checked, television is a motion picture medium.  If a broadcaster were to limit its "HDTV" sub-channel to 5Mb/s and use the remaining 15Mb/s to multiplex a variety of other services, the impact on the HDTV image would not be at all acceptable. As the image elements move, more bits are needed to transmit the compressed motion data. If the encoder is "starved" of bits to support this motion data, the results are not a pretty picture. The image elements in motion would tend to break up into non-coherent noise, call "mosquitoes."  This noise, at a distance, resolves itself to the human eye as a loss of resolution of the image edges. Also, the motion elements would tend to "smear," the faster the motion, the more the smear. If the bit starvation were severe, the image would break up into blocks. To minimize the artifacts, the HDTV sub-channel video could be severely pre-filtered before being encoded, but this would limit the video frequency response, and it would no longer appear as "HDTV" to the viewer. (Note that some pre-filtering is always done prior to encoding to minimize artifacts).</p>

<p>However, the saving grace in this technical dilemma is a neat piece of encoder technology called Statistical Multiplexing or "statmux."  Statmux technology dynamically allocates bits to the multiplex sub-channel that demands them at any particular instance. This is the same technology that allows DBS and Cable to transmit thousands of programs with practically no bandwidth. The idea is that statistically, at any instance, all of the multiplex sub-channels will not require the maximum number of bits to support an artifact free image. Therefore, those sub-channels requiring lots of bits can be taken from those sub-channels that do not. As a result, with Satmux, the broadcaster can multiplex a high definition sub-channel, along with several SDTV sub-channels or services without any appreciable artifacts.  </p>

<p>But, there is an "it depends" caveat here. Not all encoders are created equal, nor is the expertise of the broadcast (or Cable) engineers managing the encoding process. Therefore, the encoding quality depends on those factors. However, as greater encoder efficiencies are developed and broadcast expertise increases, HDTV quality improves. There is probably some degree of "let's see what we can get by with" attitude existing with some broadcasters. So, again, it is up to us to be vigilant regarding HDTV quality. If we notice compromises, we should call and complain.  </p>

<p>Therefore, to some extent,whether of not we are "ripped off" defends on us. If we accept this task, HDTV will only get better.</p>

<p>Ed</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>June 11, 2005 12:18 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 71
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
 			<h2>More on Marketplace</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Marketplace'
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
 				AND entry_id <> 71
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_are_we_being_ripped_off.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
