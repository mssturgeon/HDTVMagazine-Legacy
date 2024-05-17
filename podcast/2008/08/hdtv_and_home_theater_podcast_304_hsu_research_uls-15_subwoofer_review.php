<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1506";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1506 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
*/
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# This template is used only for podcasts
#	$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
	$container = 'article_container';
#	$category_page = 'bulletins-category.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="hsu research, transmitter subwoofer, uls subwoofer, media room, home theater, subwoofer, room, wireless, HSU, hsu, our, ULS, sound, transmitter, uls, Ara, bass, ara, feet, most, power, feel, Subwoofer, Research, line" />
	<meta name="description" content=" Listen Now - mp3 RSS Website Today's Show: Anyone who owns a home theater system knows that a Subwoofer is essential to the experience. Watching a movie in HD with surround sound is great, but some scenes require that..." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #304 HSU Research ULS-15 Subwoofer Review</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #304 HSU Research ULS-15 Subwoofer Review'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #304 HSU Research ULS-15 Subwoofer Review</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>August 22, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_PODCAST) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23304%20HSU%20Research%20ULS-15%20Subwoofer%20Review&amp;bodytext=%20Listen%20Now%20-%20mp3%20RSS%20Website%20Today%27s%20Show%3A%20Anyone%20who%20owns%20a%20home%20theater%20system%20knows%20that%20a%20Subwoofer%20is%20essential%20to%20the%20experience.%20Watching%20a%20movie%20in%20HD%20with%20surround%20sound%20is%20great%2C%20but%20some%20scenes%20require%20that...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div style="float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
		</div>
		<div style="clear:right; float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
		<div id="<?=$container?>">
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-08-22.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong></span><br id="zeva1">
 Anyone
who owns a home theater system knows that a Subwoofer is essential to
the experience. Watching a movie in HD with surround sound is great,
but some scenes require that you feel the bass. A subwoofer does just
that. An issue some have is Subwoofer placement. Its not always
convenient to place the subwoofer next to the receiver. This requires
that you run cable to a spot that is more conducive for the placement
of the speaker. The ULS-15 has a nice feature that makes this no longer
necessary. The subwoofer has a wireless connection that in most cases
works great. More on that later.<br>
<br id="q7wo1"><a title="HSU Research ULS-15 Subwoofer (MSRP $1299 - $1499)" target="_blank" href="http://hsuresearch.com/products/uls15.html" id="i.qm">HSU Research ULS-15 Subwoofer (MSRP $1299 - $1499)</a><br id="q7wo2">
<strong id="uek0">ULS-15 At a Glance</strong><br id="vdai">
<br id="q7wo3">
<div id="f7fr" align="center">
<table id="fs1v" border="1" bordercolor="#000000" cellpadding="3" cellspacing="0" height="419" width="515">
<tbody id="f7fr0">
<tr id="f7fr1">
<td style="text-align: left;" id="f7fr2" width="50%">Amplifier Power (RMS)</td>

<td style="text-align: left;" id="f7fr4" width="50%">1000W short term</td></tr>
<tr id="f7fr6">
<td style="text-align: left;" id="f7fr7" width="50%">Bass Extension</td>
<td style="text-align: left;" id="f7fr9" width="50%">15 Hz - 1 dB</td></tr>
<tr id="f7fr11">
<td style="text-align: left;" id="f7fr12" width="50%">Woofer</td>
<td style="text-align: left;" id="f7fr14" width="50%">15 Inches x 1</td></tr>
<tr id="f7fr16">
<td style="text-align: left;" id="f7fr17" width="50%">Crossover</td>
<td style="text-align: left;" id="f7fr19" width="50%">Bypassable 24 dB/Oct, continuously variable 30 - 90 Hz low pass filter</td></tr>

<tr id="f7fr21">
<td style="text-align: left;" id="f7fr22" width="50%">Phase</td>
<td style="text-align: left;" id="f7fr24" width="50%">0/180</td></tr>
<tr id="hgbf">
<td style="text-align: left;" id="hgbf0" width="50%">Inputs</td>
<td style="text-align: left;" id="hgbf1" width="50%">Balanced XLR (2), line level (2), wireless (2), speaker level (2)</td></tr>
<tr id="hgbf2">
<td style="text-align: left;" id="hgbf3" width="50%">Power Outlet Requirement</td>
<td style="text-align: left;" id="hgbf4" width="50%">600W continuous, 1200W short term</td></tr>
<tr id="hgbf5">
<td style="text-align: left;" id="hgbf6" width="50%">Ship Weight</td>

<td style="text-align: left;" id="hgbf7" width="50%">93 lbs</td></tr>
<tr id="cvy5">
<td style="text-align: left;" id="cvy50" width="50%">Dimensions</td>
<td style="text-align: left;" id="cvy51" width="50%">18.875"(h) x 18"(w) x19.25"(d) 20.25" (d)with grille</td></tr>
<tr id="cvy52">
<td style="text-align: left;" id="cvy53" width="50%">Warranty</td>
<td style="text-align: left;" id="cvy54" width="50%">7-years on the subwoofer, 2-years on electronics</td></tr>
<tr id="b6of">
<td style="text-align: left;" id="b6of0" width="50%">ULF Trim</td>
<td style="text-align: left;" id="b6of2" width="50%">16 - 50 Hz</td></tr>

<tr id="e:93">
<td style="text-align: left;" id="e:930" width="50%">Designed and Assembled in the U.S.A.</td>
<td style="text-align: left;" id="e:931" width="50%"><br id="e:932">
</td></tr></tbody></table></div><br id="e:933">
The
subwoofer is designed for what HSU Research calls a huge room. That's
6,000 cubic feet or a room that measures 20X20X15 feet. For our friends
outside the US that's approximately 160 cubic meters or a room that
measure 6X6X4.5 meters. The unit was tested in Ara's Media room that
has a volume of 2,500 cubic feet and by HSU's standards the room is
in-between a small and midsized room. <br id="q7wo28">
<br id="q7wo29">The
people at HSU Research include a diagram that provides tips on
unpacking the unit. At first I thought that was a nice touch, but after
I followed the instructions I determined that it was necessary. The
subwoofer weighs 84 pounds and is a bit difficult to get out of the
box. Following the instructions are a life saver.<br id="nz4e">
<br id="nz4e0">The
unit measures 18.875"(h) x 18"(w) x19.25"(d) 20.25" (d) with grille and
looks very nice and solidly built. The fit and finish of the ULS-15 is
superior. Its bigger than what it replaced but Ara's wife did not mind
it so much. She is either used to the big home theater gear or liked
the looks of the subwoofer. It comes in five finishes. The most basic,
Satin Black, goes for $1299 while the Piano Black goes for $1499.<br id="q7wo30">
<br id="b0c-"><strong id="b0c-0">Setup</strong><br id="b0c-1">
At
the basic level, you connect power then you connect the LFE output from
the receiver and power on. If you use wireless. You connect the LFE
output from the receiver to the wireless transmitter and you toggle the
Wired/Wireless switch to wireless and you are good to go. The
transmitter and subwoofer need to have a line of site so if your
electronics are hidden you need to bring out a cable into the room
with the subwoofer and then connect it to the wireless transmitter.<br id="v3.m">
<br id="v3.m0">There
is an included calibration CD with instructions for proper setup. We
tried the subwoofer with and without the calibration. To get the most
out of this subwoofer you will want to do the calibration. It is not
too technical but you will need a Sound Pressure Meter. You can pick
one up at Radio Shack for about $50. <br id="wu.e">

<br id="wu.e0">The
back of the unit has Volume control, phase switch, crossover in or out,
crossover frequency control, and ULF trim to compensate for room gain
at very low frequencies.<br id="a5.q">
<br id="a5.q0"><strong id="a5.q1">Sound</strong><br id="a5.q2">
Simply
amazing! The 15 inch subwoofer packs a huge punch. The sound is clear
and undistorted. According to the manufacturer the subwoofer can go
down to 10Hz in most rooms. We were not able to measure this for our
room but we can say that we were able to notice subtleties with this
subwoofer that we could not hear with our current gear.<br id="zxqc">
<br id="zxqc0">We
ran our extreme test with the Dolby True HD demo disc, the scene from
"The Last Mimzy" and we nearly brought down the walls of the house.
There was so much sound moving through the room that you could feel it.
 We also watched the Seabiscuit vs War Admiral challenge race. You
would have thought that the horses ran through our media room it was so
lifelike. The subwoofer added a higher level or reality to the movie.
So much so that Ara's kids stopped what they were doing and came
upstairs to watch (we mean feel) what was going on.
<div id="jhig"></div><br>
We
would have put in the Alien Contact Scene at the end of Close
Encounters, but we didn't want our windows blown out ;-) In general,
every movie we watched that had any LFE effects was enhanced by this
subwoofer. <br id="lqv7">
<br id="lqv70">Music benefited as well. The
bass was clean and crisp. Ara's preference is rock and roll so there is
not that real pronounced bass like there is in say hip hop. Even still
you could feel the bass line in almost everything we listened to. We
did have to turn up the volume to achieve that effect however.
Classical music benefitted from the speaker as well.<br id="u7k7">
<br id="u7k70"><strong id="u7k71">Wireless</strong><br id="u7k72">
The
wireless does what HSU advertised but a few tips will help you get the
most out of it. The manual says that the transmitter and subwoofer need
a clean line of site. When the transmitter and subwoofer were in the
same room the subwoofer performed perfectly. We were able to achieve
this at a range of about 18 feet, the maximum of Ara's Media Room.
However, moving the subwoofer less than 10 feet away in the adjacent
room resulted in unacceptable performance. HSU does not advertise a
maximum range because mileage will vary based on interference and the
layout of the room. They do recommend placing the transmitter as high
as possible to maximize the distance.
<div id="mnw."></div><br>
If
there is excessive interference on one channel you can select another
one that has less noise on it. We found that in our environment the
wireless connection provided very clean sound.<br id="lssv">

<br id="lssv0"><strong id="hy_h">Conclusion</strong><br id="lssv1">
If
you have a upper middle to high end system and a large room you should
really consider the ULS-15 subwoofer. At $1500 its not for everyone but
for those of you that can afford one won't be one bit disappointed!
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>August 22, 2008 11:10 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1506
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

			<?if (9 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 9
					AND entry_id <> 1506
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'The HT Guys'
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($qry);
				
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					echo '<div class="item"><span class="corners-top"><span></span></span>'.
					'<h2><a href="../../author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
					<h2>About The HT Guys</h2>
					<?=stripslashes($author[bio_short])?>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>
		</td><td id="right">
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
					WHERE entry_blog_id IN (1)
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$res_authors = mQuery($qry);
					while ($row_authors = mysql_fetch_assoc($res_authors)) {
						echo '<li><a href="../../../articles/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_304_hsu_research_uls-15_subwoofer_review.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
