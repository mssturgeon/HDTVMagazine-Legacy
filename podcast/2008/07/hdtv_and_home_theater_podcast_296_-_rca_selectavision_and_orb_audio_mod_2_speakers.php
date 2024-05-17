<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1477";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1477 AND placement_is_primary = 1";
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
	<meta name="keywords" content="orb audio, wall mounts, audio mod, orb speakers, rated ohms, speakers, orb, Orb, audio, subwoofer, our, Audio, room, mounts, work, small, listen, speaker, wall, mod, Mod, hand, receiver, Aperion, control" />
	<meta name="description" content="From time to time our listeners send us emails asking us to review equipment that they are interested in buying or themselves. Recently  we received an email from Kris asking us to take a look at the Orb Audio line of speakers. We were able to obtain the Orb Audio Mod 2 speakers with an upgraded Uber Ten subwoofer. The total cost for the 5.1 system was $1597 including standard ground shipping." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #296 - RCA SelectaVision and Orb Audio Mod 2 speakers</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #296 - RCA SelectaVision and Orb Audio Mod 2 speakers'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #296 - RCA SelectaVision and Orb Audio Mod 2 speakers</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>July 24, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23296%20-%20RCA%20SelectaVision%20and%20Orb%20Audio%20Mod%202%20speakers&amp;bodytext=From%20time%20to%20time%20our%20listeners%20send%20us%20emails%20asking%20us%20to%20review%20equipment%20that%20they%20are%20interested%20in%20buying%20or%20themselves.%20Recently%20%20we%20received%20an%20email%20from%20Kris%20asking%20us%20to%20take%20a%20look%20at%20the%20Orb%20Audio%20line%20of%20speakers.%20We%20were%20able%20to%20obtain%20the%20Orb%20Audio%20Mod%202%20speakers%20with%20an%20upgraded%20Uber%20Ten%20subwoofer.%20The%20total%20cost%20for%20the%205.1%20system%20was%20%241597%20including%20standard%20ground%20shipping.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-07-25.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
From time to time our listeners send us emails asking us to review equipment that they are interested in buying or themselves. Recently we received an email from Kris asking us to take a look at the <a href="http://www.orbaudio.com/" target="_blank" title="Orb Audio">Orb Audio</a> line of speakers. We were able to obtain the Orb Audio Mod 2 speakers with an upgraded Uber Ten subwoofer. The total cost for the 5.1 system was $1597 including standard ground shipping.
<br><br>
<a href="http://www.orbaudio.com/index.asp?PageAction=VIEWPROD&amp;ProdID=3" target="_blank" title="Orb Audio Mod 2 5.1 Speaker System"><strong>Orb Audio Mod 2 5.1 Speaker System</strong></a>
<br><br>
Orb Audio makes small speakers and sells direct to the consumer similar to Aperion and many other speaker manufacturers. By cutting out middle men the company is able to sell speakers a greatly reduced cost. The speakers are small in size measuring&nbsp;4 3/16" W x 5" H x 4 7/8" D with
stand and weigh 2.0625 pounds (.94 Kg). &nbsp;They also come in a center
channel orientation with the two spheres mounted side by side.
<br><br>
The
speakers are made out of American carbon steel and hand made
in the USA (California and Oregon). &nbsp;The only exception is part
of&nbsp;the&nbsp;subwoofer is made in Canada. The speakers come in 5 finishes
(Metallic Black Gloss, Pearl White Gloss, Hand Polished Steel, Hand
Antiqued Copper, and Hand Antiqued Bronze) so there will definitely be
something that the aesthetics committee will approve.&nbsp;The fit and
finish of the speakers is first rate!
<br><br>
<strong>Setup</strong><br>
Being
small means that it is easy to find a spot for the speakers. The
company sells&nbsp;wall mounts&nbsp;specifically&nbsp;designed to work with their
speakers. The single unit speakers will work with any 1/4 inch threaded
insert. The two unit speakers needed adapters to work with our mounts.
Our recommendation is to buy the wall mounts from Orb. They are
priced&nbsp;comparably&nbsp;to other wall mounts that you would buy at an
electronics store.
<br><br>
The
only setup&nbsp;issue&nbsp;we had, beyond needing adapters for the wall mounts,
was that the speakers did not accept&nbsp;Banana&nbsp;Plugs. The speakers have a
binding post that you push in to expose a small hole that you insert
the stripped speaker wire into. Letting go of the post locks the
speaker wire in place. The connection is secure but in our case it was
a pain because we had to remove the banana plugs that we use for our
regular speakers.
<br><br>
The
subwoofer was easy to connect. We connected Uber 10 to the LFE line in
from the receiver and were ready to go. The subwoofer has a Master
Power Switch, Volume Control, Phase Control, and Crossover Over Control.
<br><br>
<strong>Performance</strong>
<br>We
ran the room calibration function of the Pioneer Elite VSX-94TXH and
began to take a listen. Like&nbsp;the&nbsp;Audio Engine speakers we reviewed a
few months ago, the manufacturer recommends breaking the speakers in
before giving it a critical listen. But like the review of the Audio
Engine we couldn't wait. However, everything you read about the
performance of Orb speakers was after we spent a week playing music,
movies, and games via the Xbox 360 through them.
<br><br>
We
were very pleased with the results. Dialog in movies was clear and
precise but not as full as we would have liked. Special effects seemed
identical to&nbsp;the&nbsp;Aperion Speakers we use. The subwoofer packed a punch.
One test Ara likes doing is putting on&nbsp;the&nbsp;Dolby Blu Ray Demo Disc and
playing the scene from "The Last Mimzy". For this demo the volume is
turned up to an uncomfortable level to feel the subwoofer. The Uber 10
did a very good job with this sequence. We were continually impressed
with how well the subwoofer performed.
<br><br>
The
last test was to listen to Ode to Joy on the Immortal Beloved DVD. What
we listen for are the triangles. While we could hear them with the Orb
speakers but they weren't as&nbsp;pronounced&nbsp;as the Aperion reference
speakers Ara has in his Media Room. But still a respectable job!
<br><br>
With
music we listened to Rock and classical from both CDs and mp3s. Music
played on CDs sounded very good but again not as full as our reference
speakers. The speakers can go loud too! But be careful, our receiver
has a lot of power and we were able to distort the speakers. In
actuality this really isn't a negative because in a small room the
point where the speakers started to distort was too loud anyway. The
Orbs really shine in a smaller room.&nbsp;Mp3s sounded identical regardless
of speaker choice.
<br><br>
<strong>Other Odds and Ends</strong>
<br>
You
can try Orb Speakers in your home for 30 days without risk. Because the
only real way to make sure the speakers sound right for you is to hear
them in your environment with your equipment. Orb warranties their
speakers for 5 years and one year for&nbsp;the&nbsp;subwoofer. The Mod 2 are
rated at 4 ohms nominal, so will they work with a receiver that is
rated at 8 ohms? According to Orb Audio they work with every received
they have connected them to (no list was provided) and we can confirm
that our 8 ohm receiver had no problem with the speakers.
<br><br>
<strong>Conclusion</strong>
Overall
we were very impressed with the sound quality of these little speakers.
For a normal sized family room, bed room, or office these speakers will
do the job quite nicely. There is always a trade off when considering
form over function but with these speakers the trade off is very
minimal. Orb has other systems starting at $800.
<br><br>
<strong>RCA SelectaVision VideoDisc</strong><br>
<br>
<a href="http://www.cedmagic.com/selectavision.html" title="" target="_blank">http://www.cedmagic.com/selectavision.html</a>
<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>July 24, 2008 11:22 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1477
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
					AND entry_id <> 1477
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_296_-_rca_selectavision_and_orb_audio_mod_2_speakers.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
