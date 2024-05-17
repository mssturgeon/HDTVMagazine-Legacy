<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1466";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1466 AND placement_is_primary = 1";
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
	<meta name="keywords" content="surround sound, blu ray, home theater, high definition, right time, upgrade, HDTV, hdmi, hdtv, HDMI, speakers, content, need, get, want, receiver, home, sound, surround, high, may, theater, good, really, blu" />
	<meta name="description" content="We get email after email asking for advice on when is the right time to upgrade your home theater equipment. The answer, of course, is always &quot;right now,&quot; but that isn't always possible. Often we have to compromise in one area to upgrade another. So what components are the most important to keep up to date? Which ones can wait a while? Let's take a look." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #293 - When to Upgrade your Gear</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #293 - When to Upgrade your Gear'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #293 - When to Upgrade your Gear</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>July 14, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23293%20-%20When%20to%20Upgrade%20your%20Gear&amp;bodytext=We%20get%20email%20after%20email%20asking%20for%20advice%20on%20when%20is%20the%20right%20time%20to%20upgrade%20your%20home%20theater%20equipment.%20The%20answer%2C%20of%20course%2C%20is%20always%20%22right%20now%2C%22%20but%20that%20isn%27t%20always%20possible.%20Often%20we%20have%20to%20compromise%20in%20one%20area%20to%20upgrade%20another.%20So%20what%20components%20are%20the%20most%20important%20to%20keep%20up%20to%20date%3F%20Which%20ones%20can%20wait%20a%20while%3F%20Let%27s%20take%20a%20look.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-07-15.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
We get email after email
asking for advice on when is the right time to upgrade your home
theater equipment. The answer, of course, is always "right now," but
that isn't always possible. Often we have to compromise in one area to upgrade another. So what components are the most important to keep up to date? Which ones can wait a while? Let's take a look.<br>
<br><strong>When to Upgrade</strong><br>
<strong id="zlai0"><br>
Television</strong><br>

If
you don't have an HDTV, this is absolutely your first purchase. Beg,
borrow, bribe, do whatever you have to do to get rid of that old,
analog CRT you've had since the 80's. Now is the right time to
upgrade. If you already own an HDTV, there are some good reasons to
upgrade, but they drop in priority.<br>
<ul>
<li>HDMI:
HDMI is the wave of the future. If your TV doesn't support it, and
especially if it doesn't support HDCP, you'll want to upgrade pretty
soon. There are content protection mechanisms on media such as Blu-ray
that can, if the content owner wanted to, disable high definition
playback if you aren't using HDMI. Plus it eliminates a ton of cable
clutter.</li>

<li>EDTV: If you own one of the early EDTV
plasmas, you should start considering an upgrade. It isn't a huge
issue, as those TVs support HDTV content at 720p and 1080i, and still
look very good. But at some point you'll want a true HDTV.</li>
<li>720p:
Sure Blu-ray supports 1080p, and other content may come in the future
as well, but there's no need to run out and make a change any time
soon. 720p should last for you for a while.</li></ul><br>
<strong>High Definition Content</strong><br>
Of
course your first purchase has to be the HDTV itself, but followed very
soon after that you need high definition content to watch on that new
TV. There are several ways to do it.<br>
<ul>

<li>Over
the air: This is free, unless you need to buy an antenna. It's a great
way to get HDTV content without a new monthly fee. At some point,
however, you'll probably want to upgrade to a pay package for channels
such as ESPN, Discovery, TNT, Food Network, etc.</li>
<li>Cable,
Satellite, IPTV: These will cost money every month, but provide a lot
more content to watch. You may or may not need the extra channels, but
you sure will like them.</li>
<li>DVR: This is a must have.
You might be resisting it, but once you've tried it, you'll never be
able to go back. They're easy to get if you have an HDTV service
provider for a slight monthly fee. If you're over-the-air only, try a
Tivo.</li></ul><br>

<strong>Receiver</strong><br>
The
next big piece is surround sound. Without it you're really only
getting half of the possible HDTV experience. Surround sound requires
two parts the receiver or processor, and the speakers. If you don't
have a surround sound receiver, now is the right time to get one. If
you own one already, there are a few items that you may want to look at
as potential reasons to upgrade.<br>
<ul>
<li>Dolby
Digital: If your receiver is old it may not support decoding of the
Dolby Digital surround format. More than any other format this one is
key because it's the one used in HDTV broadcasts. This is almost a
"right now" kind of upgrade.</li>
<li>HDMI: Again with the
HDMI. There are audio formats you simply cannot enjoy if you don't
have an HDMI input on your receiver. Make sure when you do upgrade
that the HDMI input on your receiver will actually process the audio.
Some receivers just pass it thru, making the inputs almost pointless.</li>
<li>HDMI
1.3: The next step in HDMI is version 1.3. Support for this allows
your receiver to decode all the newest surround sound formats including
Dolby Digital Plus, Dolby TrueHD and DTS HD Master Audio. While this
sounds super cool, it isn't incredibly urgent because your Blu-ray
player will usually decode them for you and send the audio to the
receiver as PCM over your pre-HDMI 1.3 HDMI cable.</li></ul><br>
<strong>Speakers</strong><br>
Speakers are the one piece of your home theater that will last the longest. Invest in high quality speakers and you won't need to upgrade for decades. Reasons to upgrade may include:<br>
<ul>
<li>Not
enough speakers: You want to have at least 5 speakers: left, center,
right, and two surrounds. Ideally you'd have 7 to support all the new
formats coming out, but the real minimum is 5. Start saving up so you
can put 5 high quality speakers in your home theater. They're a great
investment.</li>
<li>Cheap speakers: If you're still using
the speakers that came with a home theater in-a-box setup, start
putting some money aside for an eventual upgrade. Sure you're getting
all the channels, so you have surround sound and the priority may not
be as high, but you'll be amazed at how the experience changes when you replace those cheap speakers with really good ones.</li>
<li>No
subwoofer: Many people think the subwoofer is optional, nothing could
be further from the truth. It turns surround sound into a surround
sound "experience" and really gets you so much close to a true movie
theater feel in your home. If you don't have one, go get one.</li></ul><br>
<strong>DVD</strong><br>
By
now DVD players are pretty much standard in every home. If you don't
have one, wander down to your local grocery store or 7-11 and pick one
up instead of your typical box of hot tamales or big gulp. Why would
you want to get a new one? There are a few reasons,<br>
<ul>

<li>Up-conversion:
Your HDTV will up-convert DVD content to match it's native resolution
of 720p or 1080p. It will do it well, but often not as well as a good
upconverting DVD player. If you really want to get the most out of
your DVD viewing, you need a good upconverting player.</li>
<li>Next
generation player: The decision is easy now, you need a Blu-ray
player. HD-DVD isn't cluttering the picture anymore (sorry for the
really bad pun). Although not a "must have" you can't argue that
Blu-ray provides the absolute highest quality high definition content
you can playback in your home. If you really want to see what your
investment in that HDTV, receiver and speakers can do, you need to get
a Blu-ray player. If you already own one, eventually you'll want to
replace it with one that supports BD-Live, but we haven't seen anything
that would make that an urgent priority at all.</li></ul><strong><br>
Other stuff</strong><br>

<ul>
<li>Cables:
Unless you're getting dropouts in the picture or sound, your five
dollar HDMI cable never needs to be upgraded to the 100 dollar one. If
you are getting dropouts, try the 20 dollar cable before wasting too
much money on stuff that won't provide any bang for the buck. We've
already talked about countless other areas where you could spend you
money more wisely.</li>
<li>Remote: If you're still using 5
remotes to control your home theater, stop it! Go pick up a universal
remote, good ones start in the 50 to 75 dollar range. Gadget freaks
like us will constantly upgrade remotes to the latest and greatest, but
you don't need to do that. Invest in a good one and it should last a
while ... unless it's stuck in safe mode :(</li>
<li>Game
systems: For as much as we tend to ignore game systems, they really
provide hours of fun for the whole family. Some of the high definition
games available on the Xbox 360 and the PS3 are amazing. If everything
else you need is already in place, consider adding a game system. They're fun, trust us.</li>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>July 14, 2008 10:40 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1466
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
					AND entry_id <> 1466
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_293_-_when_to_upgrade_your_gear.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
