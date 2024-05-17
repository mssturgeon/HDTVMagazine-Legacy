<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1532";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1532 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, kingdom crystal, indiana jones, ray version, speed racer, movie, Vudu, vudu, HDX, hdx, movies, ray, minutes, blu, Blu, audio, Ray, scenes, film, version, Crystal, review, video, new, crystal" />
	<meta name="description" content="We've both seen the entire Indiana Jones Trilogy, so when Paramount said they were filming a fourth movie we both became very excited. Braden saw the movie in the theater and Ara waited for it to come out on Blu Ray. While this review is technical in nature, be fore warned, we may throw out a few spoilers.

We talked about Vudu's new HD Technology (HDX) back on Show #316. For those of you that did not hear that show, Vudu's HDX technology, according to Vudu, is a new video format that delivers the highest quality true High Definition 1080p content available from any Internet, broadcast or satellite on-demand service. We decided to put that statement to a test." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #322 - Indiana Jones on Blu-ray and Vudu HDX</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #322 - Indiana Jones on Blu-ray and Vudu HDX'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #322 - Indiana Jones on Blu-ray and Vudu HDX</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October 23, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23322%20-%20Indiana%20Jones%20on%20Blu-ray%20and%20Vudu%20HDX&amp;bodytext=We%27ve%20both%20seen%20the%20entire%20Indiana%20Jones%20Trilogy%2C%20so%20when%20Paramount%20said%20they%20were%20filming%20a%20fourth%20movie%20we%20both%20became%20very%20excited.%20Braden%20saw%20the%20movie%20in%20the%20theater%20and%20Ara%20waited%20for%20it%20to%20come%20out%20on%20Blu%20Ray.%20While%20this%20review%20is%20technical%20in%20nature%2C%20be%20fore%20warned%2C%20we%20may%20throw%20out%20a%20few%20spoilers.%0A%0AWe%20talked%20about%20Vudu%27s%20new%20HD%20Technology%20%28HDX%29%20back%20on%20Show%20%23316.%20For%20those%20of%20you%20that%20did%20not%20hear%20that%20show%2C%20Vudu%27s%20HDX%20technology%2C%20according%20to%20Vudu%2C%20is%20a%20new%20video%20format%20that%20delivers%20the%20highest%20quality%20true%20High%20Definition%201080p%20content%20available%20from%20any%20Internet%2C%20broadcast%20or%20satellite%20on-demand%20service.%20We%20decided%20to%20put%20that%20statement%20to%20a%20test.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-24.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
We've both seen the entire Indiana Jones Trilogy, so when Paramount said they
were filming a fourth movie we both became very excited. Braden saw the
movie in the theater and Ara waited for it to come out on Blu Ray.
While this review is technical in nature, be fore warned, we may throw out a few spoilers.<br>
<br>
We talked about Vudu's new HD Technology (HDX) back on <a href="http://www.htguys.com/archive/2008/October03.html" id="e95g" target="_blank" title="Show #316">Show #316</a>.
For those of you that did not hear that show, Vudu's HDX technology,
according to Vudu, is a new video
format that delivers the highest quality true High Definition
1080p content available from any Internet, broadcast or satellite on-demand service.
We decided to put that statement to a test.<br>
<br><strong>Indiana Jones and the Kingdom of the Crystal Skull<br>
(Paramount Pictures Blu-ray) (<a href="http://www.htguys.com/shop.php?id=B001E75QGG" target="_blank" title="Buy Now">Buy Now</a>)</strong><br>
Key Tech Specs:
<ul>
<li>Released 2008 122 minutes Running time Rated PG-13</li>

<li>Video 1080p MPEG-4 AVC</li>
<li>Aspect ratio: 2.35:1</li>
<li>Audio Dolby TrueHD 5.1</li>
<li>Subtitles English, English SDH, French, Spanish and Portuguese</li>
<li>50GB Blu-ray Disc Two-disc set</li></ul><em><strong>Impression:</strong></em><br>
From
a technical point of view the movie was kind of all over the place. In
some scenes the colors were bright and vivid and in others they were
muted. We're pretty sure this was done by design. The opening desert
scenes were not as vivid as the jungle scenes. But one sequence in a
test village that was built to be blown up by nuclear blast was very
bright. The colors popped off the screen. It looked wired. It was easy
to see where scenes were shot in front of a green screen but it wasn't
too distracting. One thing we didn't notice was any film grain or any
DNR artifacts. In fact, other than the noticing some of the CGI shots,
nothing really distracted you from just watching the movie.<br>
<br>
Audio
was where the movie really shined. The subwoofer and surround speakers
were almost always doing something. The film's mixer (<a href="http://www.imdb.com/name/nm0431954/" title="Ronald Judkins">Ronald Judkins</a>) makes
use of all the speakers in a way that puts you in the action. The
Jungle Chase sequence and the film's climax in the temple are fantastic
examples of how surround sound can enhance the movie watching
experience. Kingdom of the Crystal has some booming subwoofer effects
throughout the movie. We watched it on a Saturday evening with the
windows open which required Ara to reach for the remote a few times.
More ammunition to go buy a receiver that supports Dolby Volume. This
movie is one where you are happy that you made the investment in
the equipment needed to decode Dolby True HD. &gt;<br>

<br><em><strong>Other Features:</strong></em><br>
<ul>
<li>The Return of a Legend (1080i, 17:34) </li>
<li>Pre-Production (1080i, 11 minutes)</li>
<li>Theatrical Trailers (1080p, 1:52 and 1:55)</li>
<li>Production Diary: Making 'Kingdom of the Crystal Skull' (1080i, 1:20:52)</li>
<li>Warrior Makeup (1080i, 5 minutes)</li>
<li>The Crystal Skulls (1080i, 10 minutes)</li>
<li>Iconic Props (1080i, 10 minutes)</li>

<li>The Effects of Indy (1080i, 22 minutes)</li>
<li>Adventures in Post-Production (1080i, 12 minutes)</li>
<li>Closing: Team Indy (1080i, 3 minutes)</li>
<li>Pre-Visualization Sequences (1080i, 14 minutes)</li>
<li>Still Galleries (HD) </li></ul><em><strong>Conclusion:</strong></em><br>
Indiana
Jones and the Kingdom of the Crystal Skull is a fun movie that the
whole family can watch. Blu Ray takes a fun movie and just makes it
better. The audio will make you glad that you have a home theater sound
system and if your system supports True HD you'll be even happier.<br>
<br><strong>Vudu HDX Movies (<a href="http://www.htguys.com/shop.php?id=B000VEMJFY" target="_blank" title="$229 Buy Now">$229 Buy Now</a>)</strong><br>

  Before we get into the
HDX review we thought we'd review what Vudu is for our new listeners.<br>
<br><strong><em>Vudu </em></strong>is a set top box not much bigger than a Mac Mini that delivers movies on demand. The Video is SD and HD. You don't need a computer like Amazon's Unbox or Netflix streaming videos.
Movies can be purchased and rented from the service. You need an
Internet connection for the service to work. Braden has been a big
proponent of Vudu. Our original review was completed before HD was
available. For more information please see <a id="u2wx" href="http://www.htguys.com/archive/2007/October23.html" target="_blank" title="Podcast #217">Podcast #217</a>.<br>
<br><strong>So what's the big deal about HDX?</strong>
<ul>
<li>Movies are displayed in 1080p 24 fps</li>
<li>Artifact free compression</li>
<li>640 Kbps Audio</li>

<li>No increase in price over the regular HD </li>
<li>Order online or via the remote</li></ul><br>
<strong>Audio/Video Quality:</strong><br>
We rented three
movies. Two we had HD experience with (Close Encounters of the Third
Kind, Field of Dreams) and one was new to the DVD scene (Speed Racer).
The first one we watched was Close Encounters. We did a review of it
back in January (<a id="afyw" href="http://www.htguys.com/archive/2008/January04.html" target="_blank" title="Podcast #238">Podcast #238</a>).
We found the HDX version to be almost identical to the Bu Ray version.
It had, in our opinion, the same issues the Blu Ray version had. The
daytime scenes were brilliant and the nigh shots were grainy. The audio
was good but not as good as the Blu Ray version. The bass seemed a bit
flat. The musical sequence at the end was better in True HD but still
very enjoyable.<br>
<br>
Next up was Field of Dreams.
Ara really likes this movie and rented it on HD DVD back when he owned
a player. The movie has some very good day shots that would be ideal
for HDX test. In this case the HDX version also delivered. We really
couldn't see any artifacts other than a little film grain on our 65
inch DLP. Actually, on older movies about the only real complaint we
have is that you can see the film grain in some scenes. Audio was fine,
but Field of Dreams doesn't have much in the way of audio. The
subwoofer was pretty much quiet throughout the whole movie.<br>
<br>
Last
up was Speed Racer. Being a new film we expected more from it. HDX
delivered again. If you are not familiar with the movie, its very
cartoony and animated movies do well in HD. For Speed Racer we compared
DVD (which looked very good), AppleTV HD (which looked better than the
DVD), and HDX, which was the hands down winner. The colors popped off
the screen. During the entire movie we saw only two very brief
compression issues. Other than that, the movie was pristine. Sound was
great as well. There was plenty to keep the subwoofer going and the mix
was quite lively. This was the best of the three movies we picked.<br>
<br>

All
movies took a minimum of four hours to download so planning ahead is
required. The downloads were started in the morning so by the time the
evening rolled around the movies were ready to be watched.<br>
<br><strong>Conclusion:</strong><br>
There
is no better video quality that can be had via download. At roughly the
same price as a Blu Ray player, the Vudu player is a no-brainer. With
the ability to archive some titles to the Vudu servers, it almost makes
buying discs obsolete. There still isn't a Vudu box for the car and you
can't watch content from one Vudu on another Vudu. Maybe some day?<br>
<br><br>

		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October 23, 2008 11:39 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1532
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
					AND entry_id <> 1532
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_322_-_indiana_jones_on_blu-ray_and_vudu_hdx.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
