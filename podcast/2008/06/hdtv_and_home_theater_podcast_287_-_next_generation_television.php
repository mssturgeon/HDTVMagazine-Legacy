<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1445";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1445 AND placement_is_primary = 1";
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
	<meta name="keywords" content="mediasmart connect, internet connectivity, digital media, separate devices, vudu appletv, internet, Internet, media, HDTV, hdtv, need, box, mediasmart, content, really, connectivity, Media, based, MediaSmart, Connect, connect, USB, Box, video, usb" />
	<meta name="description" content="DisplaySearch recently released a report about what TV manufactures will need to do to sell more televisions in the coming years.  One of the key features was network, and specifically Internet, connectivity.  Just a couple days prior to that report we received a press release about the availability of HP's MediaSmart Connect for pre-order.  We saw the demo at CES and liked the idea.  All of this got us thinking;  DisaplySearch says TV's need Internet connectivity, HP agrees in theory, but prefers the set top box route.  If we could design our own Internet connected TV or STB, what would it do.  From the &quot;If the HT Guys ruled the world...&quot; point of view." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #287 - Next Generation Television</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_287_-_next_generation_television';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #287 - Next Generation Television'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_287_-_next_generation_television.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #287 - Next Generation Television</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>June 24, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_287_-_next_generation_television.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_287_-_next_generation_television.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_287_-_next_generation_television.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_287_-_next_generation_television.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23287%20-%20Next%20Generation%20Television&amp;bodytext=DisplaySearch%20recently%20released%20a%20report%20about%20what%20TV%20manufactures%20will%20need%20to%20do%20to%20sell%20more%20televisions%20in%20the%20coming%20years.%20%20One%20of%20the%20key%20features%20was%20network%2C%20and%20specifically%20Internet%2C%20connectivity.%20%20Just%20a%20couple%20days%20prior%20to%20that%20report%20we%20received%20a%20press%20release%20about%20the%20availability%20of%20HP%27s%20MediaSmart%20Connect%20for%20pre-order.%20%20We%20saw%20the%20demo%20at%20CES%20and%20liked%20the%20idea.%20%20All%20of%20this%20got%20us%20thinking%3B%20%20DisaplySearch%20says%20TV%27s%20need%20Internet%20connectivity%2C%20HP%20agrees%20in%20theory%2C%20but%20prefers%20the%20set%20top%20box%20route.%20%20If%20we%20could%20design%20our%20own%20Internet%20connected%20TV%20or%20STB%2C%20what%20would%20it%20do.%20%20From%20the%20%22If%20the%20HT%20Guys%20ruled%20the%20world...%22%20point%20of%20view.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-06-24.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>


<div><strong><span id="yqc03">Today's Show:</span></strong><span id="yqc03"><br>

</span>DisplaySearch
recently released a report about what TV manufactures will need to do
to sell more televisions in the coming years.&nbsp; One of the key features
was network, and specifically Internet, connectivity.&nbsp; Just a couple
days prior to that report we received a press release about the
availability of HP's MediaSmart Connect for pre-order.&nbsp; We saw the demo
at CES and liked the idea.&nbsp; All of this got us thinking;&nbsp; DisaplySearch
says TV's need Internet connectivity, HP agrees in theory, but prefers
the set top box route.&nbsp; If we could design our own Internet connected
TV or STB, what would it do.&nbsp; From the "If the HT Guys ruled the
world..." point of view.<br id="b:uq">
<strong id="qe1q"></strong></div>

<div><strong id="qe1q">&nbsp;</strong></div>
<div><strong id="qe1q">Next Generation Televisions</strong><br id="b:uq0">
<strong id="qe1q0">MediaSmart Connect</strong><br id="b:uq1">
<em id="qe1q1"><br id="b:uq2">
PALO
ALTO, Calif., June 17, 2008 – HP today announced that HP MediaSmart
Connect, a sleek digital media receiver that brings digital media from
around the home or Internet (1) to any HDTV, is now available for
pre-order.<br id="b:uq3">
<br id="b:uq4">First announced as part of the
HP product launch at the 2008 Consumer Electronics Show, HP MediaSmart
Connect features a sleek design with a piano-black finish as well as
HP's exclusive "Zen" Imprint pattern and ambient blue lighting, making
it a stylish addition to any room in the home.<br id="b:uq6">
</em>
<ul id="b.68">
<li id="b.680"><em id="qe1q1">Play your music, photos, and video from any Windows-based PC in your house on your HDTV1,2</em></li>
<li id="b.681"><em id="qe1q1">Direct access to OnLine Media Services3,4</em></li>
<li id="b.682"><em id="qe1q1">Integrated Windows Media Center Extender functionality5</em></li>

<li id="b.683"><em id="qe1q1">HP Pocket Media Drive bay6</em></li>
<li id="b.684"><em id="qe1q1">Built-in wired and wireless networking - IEEE 802.11a/b/g/n7,3</em></li>
<li id="b.685"><em id="qe1q1">HDMI, Component video, and digital audio outputs</em></li>
<li id="b.686"><em id="qe1q1">Media playback from USB-based portable storage media</em></li></ul><strong id="o.3b"><br id="camh">
The Ultimate TV/STB, "the Box"</strong><br id="camh0">
<em id="o.3b0"><br id="o.3b1">
Broadcast based HDTV</em><br id="camh1">
The
TV should still be primarily a TV.&nbsp; The Box would need an ATSC tuner or
two and the ability to accept CableCARD or the upcoming DirecTV Card
technology.&nbsp; <br id="c6i5">

<br id="qnrj"><em id="e7-p">Internet Connectivity optimized for streaming video</em><br id="qnrj0">
Build
in the Roku style Netflix streaming, or the ability to get straight to
Hulu content.&nbsp; Optimize it for content we actually want on the TV. <br id="qnrj1">
<br id="c6i50"><em id="c6i51">Storage</em><br id="c6i52">
We
can't live without our DVRs.&nbsp; If we can watch broadcast TV on there,
we'd need to be able to record, and schedule future recordings.<br id="l5zu">
<br id="l5zu0"><em id="acq7">Internet based Interactivity</em><br id="acq70">
Let's
face it, the interactivity on Cable, DirecTV and Dish set tops is
really bad.&nbsp; The interactivity on Vudu and AppleTV is really good.&nbsp;

Rout that traffic over the Internet, it just works better.<br id="acq71">
<br id="acq72"><em id="lucp">HDTV Movie Rentals</em><br id="ga.g">
Why re-invent the wheel?&nbsp; OEM technology from Vudu or AppleTV for HDTV movie rentals, they've got it down.<br id="ltg4">
<br id="ltg40"><em id="lucp0">LAN Connectivity</em><br id="ltg41">
All
content, and tuners, on the Box should be visible to other Boxes on the
local network.&nbsp; Let's make this vaporware dream a reality - in full
high def, though.<br id="oj5l">
<br id="oj5l0"><em id="lucp1">USB Port</em><br id="oj5l1">
USB Port and the ability to automatically export content to a portable device in a "portably fiendly" format like MP4.<br id="oj5l2">

<br id="mgl9"><em id="mgl90">Bottom line</em><br id="oj5l3">
Really
it just sounds more like the Ultimate DVR.&nbsp; Whether or not it's built
into the TV or in a set top box, who cares?&nbsp; TiVo is really close to
this, and they're hurting trying to sell against the "free" DVRs from
Cable and Satellite, so maybe there's more to it.&nbsp; We say that "if this
device existed, we'd buy it in a heartbeat" but price is a big
concern.&nbsp; If the separate devices all add up to less cost that the one,
most people will still go separate.&nbsp; And with separate devices you can
"phase" in the functionality.&nbsp; So we need it all, but we need it cheap
too!

		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>June 24, 2008 12:44 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1445
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
					AND entry_id <> 1445
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_287_-_next_generation_television.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
