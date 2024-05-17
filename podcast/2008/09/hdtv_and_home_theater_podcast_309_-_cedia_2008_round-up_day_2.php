<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1542";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1542 AND placement_is_primary = 1";
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
	<meta name="keywords" content="inch inch, inch screen, cedia round, looked good, rear projection, technology, screen, inch, get, room, power, good, projector, demo, surround, plasma, effect, look, LCD, Saw, starlift, custom, levels, looked, Samsung" />
	<meta name="description" content="CEDIA 2008 Round-up, Day 2: Samsung, Sony (again), Mitsubishi, Yamaha, Audessey, much, much more!" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #309 - CEDIA 2008 Round-Up, Day 2</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #309 - CEDIA 2008 Round-Up, Day 2'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #309 - CEDIA 2008 Round-Up, Day 2</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>September  8, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23309%20-%20CEDIA%202008%20Round-Up%2C%20Day%202&amp;bodytext=CEDIA%202008%20Round-up%2C%20Day%202%3A%20Samsung%2C%20Sony%20%28again%29%2C%20Mitsubishi%2C%20Yamaha%2C%20Audessey%2C%20much%2C%20much%20more%21&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-09-09.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
<strong>CEDIA 2008 Round-Up, Day 2</strong><br>
<br>
<div id="e_0l1"><a id="d82x" href="http://www.samsung.com/us/" target="_blank" title="Samsung"><strong id="emrm">Samsung</strong></a> </div>
<div id="e_0l5"><strong id="emrm0">LED LCDs</strong>
- Look pretty near as good as Plasma.&nbsp;Available in 40-inch, 46-inch,
52-inch and 57-inch diagonal screen sizes, Samsung's new "local
dimming," LED (light-emitting diode) technology provides a brightness
level of 450nits, and reduces power consumption by as much as 30
percent.</div>
<div id="vwj:">&nbsp;</div>
<div id="vwj:1">Available this&nbsp;Holiday&nbsp;season. No pricing details yet.</div>

<div id="mfpk0">&nbsp;</div>
<div id="mfpk2"><strong id="emrm1">3D Ready Plasma</strong>
- Had a video game running. Looked very realistic. This type of
technology would work in games but I find it gimmicky in actual movies.</div>
<div id="yrzb0">&nbsp;</div>
<div id="yrzb2"><span style="font-weight: normal;" id="p4-20"><a id="hrxf" href="http://www.sony.com/" target="_blank" title="SONY"><strong id="emrm2">SONY</strong></a> </span><strong id="emrm3">&nbsp;Again</strong></div>
<div id="p4-25">After
seeing the Samsung LED LCD, I decided to go to SONY and check out their
implementation (XBR8). They had the display in a darkened room so you
could really see the blacks. Again, it looked as good as a plasma. The
contrast was fantastic! These units will sell at a&nbsp;premium.&nbsp;</div>
<div id="v40h">&nbsp;</div>
<div id="v40h1"><a id="s2iu" href="http://www.mitsubishi-tv.com/" target="_blank" title="Mitsubishi"><strong id="emrm4">Mitsubishi</strong></a> </div>

<div id="v40h5">Saw
the LaserVue DLP. Color and contrast looked good but there was a
distracting shimmering that made the Silk Screen Effect look worse. The
best way to describe the effect is it looks like the effect you get
when you point a laser pointer at something that&nbsp;diffuses&nbsp;the beam.
This is a first generation so it should get better. Some Facts about
the LaserVue:</div>
<div id="i4s.1">
<ul id="r5li">
<li id="r5li0">1/4 the power of Plasma</li>
<li id="r5li1">1/3 the power of LCD</li>
<li id="r5li2">1/2 the power of DLP</li>
<li id="df7c">Available late September early October via dealers only</li>
<li id="df7c0">$7000</li></ul>
<div id="n1001">When you factor in the price. Pick up the regular DLP instead. It did not look $4000 better.</div>

<div id="islj">&nbsp;</div>
<div id="islj1"><a id="fx6y" href="http://stewartfilmscreen.com/" target="_blank" title="Stewart Film Screen"><strong id="emrm5">Stewart Film Screen</strong></a></div></div>
<div id="v40h7">Saw
a very cool screen called StarGlas 60. Its a rear projection screen
that is made out of glass. You project from behind. There are none
of&nbsp;the&nbsp;screen effects that you see on rear projection TVs. They had it
displayed with their StarLift product. StarLift is a&nbsp;mechanism&nbsp;that
houses the StarGlas. You need to provide your own custom cabinet.&nbsp;</div>
<div id="tufr">&nbsp;</div>
<div id="tufr1">The
application they were touting was putting the StarLift at the end of
your bed and with a press of a button as 60 inch screen would rise
automatically. Potentially you would have a projector in an armoire and
that would be your source. Obviously this is not the only application
for these products.&nbsp;</div>
<div id="kxwc">&nbsp;</div>
<div id="kxwc1">The
whole thing would set you back about $15K without the projector or
custom cabinet. At least we all have something to dream about!</div><br id="emrm6">
<div id="nywa"><a id="vphm" href="http://www.yamaha.com/yec/?lid=SPLASH_TOP_YECHome&amp;lpos=A" target="_blank" title="Yamaha"><strong id="eybu12">Yamaha</strong></a> </div>

<div id="vg2g2">Saw
a great demo of the Yamaha YSP-4000 Digital Sound Projector. The demo
room was not a pure four wall room and there was a distinct surround
sound effect. It has a MSRP of 1799. We are going to try and get a demo
version for review. If it can make good surround&nbsp;in my Master bedroom
It will find a permanent place in my home. There are two HDMI, two
component and three composite inputs. &nbsp;</div>
<div id="eutk">&nbsp;</div>
<div id="eutk1">BTW, if you have $1000 burning a hole in your pocket you can pick up the new&nbsp;<a id="bzox" href="/shop.php?id=B001EQI9CU" target="_blank" title="BD-S2900 Blu Ray Player">BD-S2900 Blu Ray Player</a>. For that money you get&nbsp;<span style="line-height: 16px;" id="siif"><font id="eybu15" size="2">BD
Final Standard Profile (Profile 1 v1.1) for added BonusView features
and a front panel memory card slot&nbsp;for JPEG and AVCHD playback, plus
storage of BonusView material.</font></span></div>
<div id="ce1y">&nbsp;</div>
<div id="ce1y2"><span style="line-height: 16px;" id="iira"><a id="zrq9" href="http://www.audyssey.com/" target="_blank" title="Audessey"><strong id="eybu21">Audessey</strong></a></span><span><br>
This
is a&nbsp;competing&nbsp;technology to Dolby Volume. It works a
little&nbsp;differently&nbsp;but it does essentially the same thing. Actually,
there are three technologies that work together. First there is MultEQ.
This is an automated process that equalizes the room. This is done
once. Then there is Dynamic EQ. This technology continually adjusts the
EQ to account for the level you are listening at. The issue is really
when you listen at low levels the EQ set at reference levels do not
hold up. Dynamic EQ adjusts the EQ based on human perception and room
acoustics. The third piece of the puzzle is Dynamic volume. This is
where the volume you are listening to is maintained&nbsp;across&nbsp;all passages
while maintaining the surround experience, especially at low levels.</span><span><br>

<br>They
provided a nice demo with music, pure dialog, and loud surround
effects. &nbsp;The technology will look ahead in the audio and make the
adjustments. We can't say which technology is better, Dolby or Audessy.
We'll have to wait until we can get units with both and pick our own
material to run through it. Audyssey does have one advantage however,
you can buy a receiver with this technology for less than $1000.</span><span style="line-height: 16px;" id="qa853"><font id="eybu31" size="2">&nbsp;</font><a id="bd:m" href="/shop.php?id=B001BKR920" target="_blank" title="Denon AVR2309CI $850">Denon AVR2309CI $850</a><font id="eybu36" size="2">.</font></span></div>
<div id="nywa1">&nbsp;</div>
<div id="nywa3"><a id="s0_1" href="http://www.epson.com/cgi-bin/Store/HomeEntertainment/heindex.jsp?BV_UseBVCookie=yes&amp;oid=-11521" target="_blank" title="Epson"><strong id="eybu37">Epson</strong></a> </div>
<div id="u9ni2">Epson was awarded the AVS Forum innovation award for their&nbsp;<a id="j.ds" href="http://www.ensemblehd.com/" target="_blank" title="Ensemble HD 720">Ensemble HD 720</a>
&nbsp;This is truly a Home theater&nbsp;in a box system. Actually it comes in
several boxes. You can chose between the Ensemble HD 1080 for $7000 or
the Ensemble HD 720 for $5000. You get a projector, 100 inch screen, a
custom designed Atlantic Technology 5.1 system with a 10 inch
subwoofer, DVD Player, LCD remote, and color coded brackets, cables,
and wire management.</div>
<div id="uw.f">&nbsp;</div>
<div id="uw.f1">We
have friends who put a theater together in 3 hours. If you don't want
to fish wires through the walls they provide channel locks that can be
painted. The instructions are very well written down to which bag a
particular screw is in.</div>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>September  8, 2008 11:13 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1542
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
					AND entry_id <> 1542
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_309_-_cedia_2008_round-up_day_2.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
