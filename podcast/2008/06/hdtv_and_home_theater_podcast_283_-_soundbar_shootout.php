<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1426";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1426 AND placement_is_primary = 1";
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
	<meta name="keywords" content="back lighting, surround sound, home theater, hdmi inputs, passive design, soundbar, sound, subwoofer, surround, speaker, hdmi, inputs, HDMI, marantz, system, design, audio, Cons, pros, receiver, panel, Yamaha, speakers, Pros, Marantz" />
	<meta name="description" content="Home Theater Magazine recently put a panel together to look at five different soundbars.  Since we've only ever used the Yamaha Sound projector, and often get questions about models from other manufacturers, we thought it would be valuable to share their results.  The full article, called 'The Power of One' was written by Adrienne Maxwell.  It's certainly worth reading if you're in the market for a soundbar, since we won't cover all the details.  The five models tested were from Philips, Marantz, Yamaha, Denon, and Polk." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #283 - Soundbar Shootout</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_283_-_soundbar_shootout';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #283 - Soundbar Shootout'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_283_-_soundbar_shootout.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #283 - Soundbar Shootout</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>June 11, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_283_-_soundbar_shootout.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_283_-_soundbar_shootout.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_283_-_soundbar_shootout.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_283_-_soundbar_shootout.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23283%20-%20Soundbar%20Shootout&amp;bodytext=Home%20Theater%20Magazine%20recently%20put%20a%20panel%20together%20to%20look%20at%20five%20different%20soundbars.%20%20Since%20we%27ve%20only%20ever%20used%20the%20Yamaha%20Sound%20projector%2C%20and%20often%20get%20questions%20about%20models%20from%20other%20manufacturers%2C%20we%20thought%20it%20would%20be%20valuable%20to%20share%20their%20results.%20%20The%20full%20article%2C%20called%20%27The%20Power%20of%20One%27%20was%20written%20by%20Adrienne%20Maxwell.%20%20It%27s%20certainly%20worth%20reading%20if%20you%27re%20in%20the%20market%20for%20a%20soundbar%2C%20since%20we%20won%27t%20cover%20all%20the%20details.%20%20The%20five%20models%20tested%20were%20from%20Philips%2C%20Marantz%2C%20Yamaha%2C%20Denon%2C%20and%20Polk.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-06-10.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
<a href="http://www.hometheatermag.com/" id="szm6" target="_blank" title="Home Theater Magazine">Home Theater Magazine</a> recently put a panel together to look at five different soundbars. Since we've only ever used the Yamaha Sound projector, and often get questions about models from other manufacturers, we thought it would be valuable to share their results.  The full article, called '<a href="http://www.hometheatermag.com/compactspeakers/408sound/index.html" target="_blank" title="The Power of One">The Power of One</a>' was written by Adrienne Maxwell. It's certainly worth reading if you're in the market for a soundbar, since we won't cover all the details.  The five models tested were from Philips, Marantz, Yamaha, Denon, and Polk.</p>

<p><strong>Soundbar Shootout</strong></p>

<p><strong>What is a Soundbar?</strong><br />
A soundbar is one speaker that simulates the 5 speakers needed for surround sound in a home theater.  They typically look like slightly wider center channel speakers, and tend to be pretty flat, to match the design aesthetic of flat panel televisions. Each one is slightly different, but they will typically use properties of sound, reflection and the human ear to make you believe that there is sound coming from all around the room, not just right in front of you. Often times the success of this simulated surround sound depends on the size and shape of the room, the placement of large objects in the room and the placement of the speaker itself.</p>

<p><strong>Why use a Soundbar?</strong><br />
It's a no-brainer in many circumstances.  If you could get the full 5.1 surround experience, and only have to install one speaker, why would you use anything else?  As it turns out, they do a good, but not perfect, job of creating an enveloping audio experience.  The main reason for sacrificing a little bit of sound  is convenience.  There is only one speaker to install, and it is located either directly below or directly above your TV.  There are no wires to run or rear channel speakers to try to place in the room.  They make surround sound very easy.  For secondary environments, or a temporary living situation, they are a perfect fit.</p>

<p><strong>5. Philips HTS8100 ($800)</strong><br />
<em>&quot;Literally a home theater in two boxes, the HTS8100 includes a subwoofer and a soundbar connected via a long proprietary cable. The subwoofer houses the amplification for its own 6.5-inch woofer and the three speakers within the soundbar. That means you don't need a separate A/V receiver. Instead, you connect sources directly to the HTS8100 ... The HTS8100 is the only system in the test that includes both a DVD player and an iPod dock.&quot;</em></p>

<p><em>Pros</em><br />
<ul><li><em>Least expensive model</em></li><li><em>Very nice looking</em></li><li><em>Good subwoofer performance</em></li><li><em>Nice job with dialog</em></li><li><em>Widened the sound stage</em></li><li><em>Includes DVD player and iPod dock</em></li><li><em>Includes subwoofer</em></li></ul><br />
<em>Cons</em><br />
<ul><li><em>Only one digital audio input</em></li><li><em>No back-lighting on remote</em></li><li><em>Advanced design could break easily (sliding DVD door)</em></li><li><em>Lack of mid-range audio</em></li><br />
<li><em>No convincing side or rear effects</em></li></ul></p>

<p><strong>4. Marantz ES7001 ($1,400)</strong><br />
<em>&quot;Like the Philips model, Marantz's ES7001 is an active soundbar that contains its own amplification and input panel ... Marantz adds HDMI switching in the form of two HDMI 1.1 inputs and one HDMI output. The inputs are passthrough only, providing no upconversion or deinterlacing, and they will pass 1080p/24 or 1080p/60.&quot;</em></p>

<p><em>Pros</em><br />
<ul><li><em>Great dynamic ability and a full midrange sound</em></li><li><em>Widened the sound stage</em></li><li><em>Better built-in speakers</em></li><li><em>Fuller, less bright, more dynamic sound</em></li></ul><br />
<em>Cons</em><br />
<ul><li><em>Doesn't include a subwoofer</em></li><li><em>Very heavy</em></li><li><em>Doesn't include mount or base</em></li><li><em>No back-lighting on remote</em></li><li><em>Audio sounds overly processed</em></li><li><em>No surround feeling/imprecise multichannel imaging</em></li><li><em>High price<br id="quwx4"></em></li></ul></p>

<p><strong>3. Yamaha YSP-3000 ($1,200)</strong><br />
<em>&quot;While other audio companies are just entering the soundbar market, Yamaha is on their third generation of digital sound<br />
projectors. The new YSP-3000 falls in the middle of the third-generation lineup, between the $1,800 YSP-4000 and the $900 YSP-900. This is yet another active system with its own amplification and a connection panel that looks more like a receiver than a speaker:<br />
two HDMI inputs, one HDMI and composite video output, four digital audio inputs (two optical, two coaxial), two stereo analog inputs, a 3.5mm aux input, and a subwoofer preout to connect an optional subwoofer like the Polk PSW111 we used. As with the Marantz model, the HDMI inputs are passthrough only and can accept both 1080p/24 and 1080p/60.&quot;</em></p>

<p><em>Pros</em><br />
<ul><li><em>Small and compact/easy to place</em></li><li><em>Multi-function remote</em></li><li><em>Automatic setup and calibration process, using the supplied IntelliBeam microphone</em></li><li><em>The most realistic surround simulation</em></li></ul><br />
<em>Cons</em><br />
<ul id="iugk4"><li><em>Somewhat boxy design</em></li><li><em>No included wall brackets or tabletop base</em></li><li><em>Soundstage seemed muddy</em></li><li><em>No back-lighting on remote</em></li><li><em>Needs a subwoofer (not included)</em></li></ul></p>

<p><strong>2. Denon DHT-FS3 ($1,199)</strong><br />
<em>&quot;Like the Philips soundbar, the DHT-FS3 is a proprietary system that includes both the soundbar and subwoofer. Boasting the smallest footprint in the group, the soundbar is less than 34 inches wide and 4 inches tall, and the sub is about the size and shape of an entry-level A/V receiver. Both have an attractive, gloss-black finish.&quot;</em></p>

<p><em>Pros</em><br />
<ul><li><em>Includes subwoofer</em></li><li><em>Setup is very simple</em></li><li><em>Includes wall-mount brackets and two types of screw-in feet</em></li><li><em>Solid dynamic range for a small system</em></li><li><em>Believable sense of envelopment</em></li></ul><br />
<em>Cons</em><br />
<ul><li><em>No back-lighting on remote</em></li><li><em>Lacks HDMI</em></li><li><em>Harsh in the high ranges</em></li><li><em>Serious center channel issues</em></li><li><em>Overwhelming if you're in the sweet spot, better if you're off-axis</em></li></ul></p>

<p><strong>1. Polk SurroundBar 50 ($1,100)</strong><br />
<em>&quot;it's a passive design with no internal amplification, input panel, or digital signal processing. That means you need to add an external A/V receiver or amplifier, so the setup process (and total cost) is similar to that of a more traditional HT system. You run five sets of speaker wire to the soundbar's five gold-plated binding posts (which accept banana plugs) and perform speaker setup via your receiver's setup menu.&quot;</em></p>

<p><em>Pros</em><br />
<ul><li><em>Passive design (more traditional speakers)</em></li><li><em>Best sound by far</em></li><li><em>Very natural sound</em></li><li><em>Clear dialog reproduction</em></li><li><em>Elegant aesthetic</em></li></ul><br />
<em>Cons</em><br />
<ul><li><em>Passive Design (no integrated amp)</em></li><li><em>Slightly more complicated setup</em></li><li><em>Needs a subwoofer (not included)</em></li><li><em>Not as much envelopment as some of the others</em></li><li><em>Not an &quot;out of the box&quot; solution</em></li></ul></p>

<p><strong>Conclusion</strong><br />
Overall the Polk sounded the Best, the Yamaha provided the best simulated surround sound and the Denon provided the best all around package.  But none of them were actually able to replace a true 5.1 system.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>June 11, 2008 11:01 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1426
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
					AND entry_id <> 1426
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/06/hdtv_and_home_theater_podcast_283_-_soundbar_shootout.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
