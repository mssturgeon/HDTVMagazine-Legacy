<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1487";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1487 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, san diego, digital channels, master audio, uhf antenna, antenna, Ara, ara, channels, movie, miles, audio, digital, blu, ray, Blu, Patton, patton, test, transmitters, Yagi, see, yagi, much, san" />
	<meta name="description" content="We talk about one of Ara's favorite movies, Patton. Over the weekend he
watched it on Blu Ray and will tell you if Blu Ray breathes new life into the nearly 40 year old movie. And with the analog cutoff around the corner we found a small outdoor antenna that we that will make the
transition a bit easier for you." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #298 - Patton on Blu-ray and ClearStream 2 UHF Antenna</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #298 - Patton on Blu-ray and ClearStream 2 UHF Antenna'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #298 - Patton on Blu-ray and ClearStream 2 UHF Antenna</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>July 31, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23298%20-%20Patton%20on%20Blu-ray%20and%20ClearStream%202%20UHF%20Antenna&amp;bodytext=We%20talk%20about%20one%20of%20Ara%27s%20favorite%20movies%2C%20Patton.%20Over%20the%20weekend%20he%0Awatched%20it%20on%20Blu%20Ray%20and%20will%20tell%20you%20if%20Blu%20Ray%20breathes%20new%20life%20into%20the%20nearly%2040%20year%20old%20movie.%20And%20with%20the%20analog%20cutoff%20around%20the%20corner%20we%20found%20a%20small%20outdoor%20antenna%20that%20we%20that%20will%20make%20the%0Atransition%20a%20bit%20easier%20for%20you.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-08-01.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
We
talk about one of Ara's favorite movies, Patton. Over the weekend he
watched it on Blu Ray and will tell you if Blu Ray breathes new life
into the nearly 40 year old movie. And with the analog cutoff around
the corner we found a small outdoor antenna that we that will make the
transition a bit easier for you.<br><br>Patton (<a id="zzc9" href="/shop.php?id=B00158K0S8" target="_blank" title="Buy Now">Buy Now</a>)</strong>The
movie won seven Oscars when it was released in 1970 and is one of Ara's
favorite movies. Ara has seen it at least ten times over the years and
it has a permanent place on his movie server. So with the recent
release on Blu Ray Ara wanted to see if the movie benefited from the
new technology.<br><br><em id="z5ve3">Key Tech Specs:</em>
<ul id="true">
<li id="ee95">Blu-ray 50GB Dual-Layer/DVD-9</li>

<li id="ee950">Video: 1080p/AVC MPEG-4</li>
<li id="ee951">Aspect Ratio: 2.20:1</li>
<li id="ee952">English DTS HD Lossless Master Audio 5.1 Surround</li></ul>
<br><br><em>Impression:</em><br>
The
movie was every bit as good as Ara remembered it. The added benefit of
HD just made it that much better. Fox did an incredible job with the
transfer. Colors jumped off the screen. There was so much more detail
to grab your attention. The movie was encoded in 1080p AVC and watching
on Ara's 65 inch DLP was like seeing it for the first time. Fox either
had a pristine print or did some great restoration because we did not
see any film damage throughout the entire movie.<br><br>Audio
was a bit of a disappointment for Ara. Every time we see the Dolby True
HD or DTS Master Audio light up we expect great things. Patton has
a DTS HD Lossless Master Audio 5.1 so the expectation was very high.
Unfortunately the audio did not deliver. This is not a knock against
next generation audio, its a knock against the audio on this disc. Very
few explosions woke up the subwoofer and the listener never felt
"surrounded" by the sound.<br><br>
<em>Other Features:</em><br>
There is an introduction by Francis
Ford Coppola and the usual "Making of" type of content. One feature
that Ara found interesting was entitled "Patton's Ghost Corps". Its a
documentary about the soldiers he left behind when he went to assist in
the "Battle of the Bulge". It paints a very different picture of the
General. One that Ara found quite eye opening.<br><br>
<em id="z5ve6">Conclusion:</em><br>If
you liked the movie when it was released in the theater of on DVD you
will love what they did with the Blu Ray version. Patton sets the bench
mark for how to take a Library film and bring it into the digital age!<br><br>
<strong>ClearStream 2 (C2) UHF Antenna (<a href="/shop.php?id=B0017O3UHI" target="_blank" title="Buy Now">Buy Now</a>)</strong><br><br>
If
you have an HDTV or are thinking about getting one before the Analog
Cutoff early next year you may also want to consider picking up your
digital TV via an antenna. Many of you are concerned that an antenna is
an eye sore of that they are just too big to to deal with. Today we
talk about an antenna that takes care of both those issues.<br><br>
The C2 is less that 1 foot by 2 feet and has an advertised range of up to
50 miles. According to the manufacturer (Antennas Direct) the CS2 uses
a new breakthrough in size and unmatched ultra efficient design and
gain. So in typical HT Guys fashion we look at this
antenna&nbsp;empirically.<br><br>
<strong>Tech Specs</strong>
<ul id="hwps1">
<li id="hwps2">Range Up to 50 Miles</li>

<li id="hwps3">Gain of 10.2 dBi</li>
<li id="hwps4">Consistent gain through the entire DTV channel spectrum</li>
<li id="hwps7">Dimensions: 20”H x 10”W x 5”D</li></ul>
<br><br>
The
test site for this review is 45 miles from the LA transmitters and 80
miles from the San Diego Transmitters. By the way, one San Diego
station (Fox) is over a hundred miles away in Mexico and were able to
pull it in. The current setup for OTA is a&nbsp;<a id="ybou" href="http://www.winegarddirect.com/viewitem.asp?p=SHD9095" target="_blank" title="Winegard HD 9095P">Winegard HD 9095P</a>.
This is a Yagi style antenna rated for deep fringe. It costs about $115
including shipping. The antenna has a 9 foot boom and has a low wife
acceptance factor, not to mention HOA, and neighbor acceptance as well.
With this antenna we can receive all digital channels (45) from LA and
a few from San Diego.<br><br>
The testing was very straight forward. Put the C2 up against the monster Yagi and see how well it did. It should be noted that Ara lives in a hilly area and that there is a house directly behind and above him. So this is not a best case scenario in the least.<br><br>
There were three tests involved. The first was to stick the antenna in a window and scan for digital channels. We weren't expecting much success here but much to our surprise the C2 picked up 9 digital channels. Some of them were ones we actually watch. The interesting thing was that we picked up two channels from San Diego. Ara has mentioned it in the past but it warrants mentioning again, there are mountains off to the East and the antenna is probably picking up a reflection.<br><br>
The next test was to actually aim the antenna in the direction of the transmitters and hold it outside the window. With this test we were able to pull in 12 channels. We were hoping for more because at this point it has not been a fair test and the only way to do so was to get up on the roof and put the C2 right next to the Yagi. Anything close to 45 would have kept Ara off the roof which looks a lot higher when you are up there looking down.<br><br>
For the third and final test we mounted the antenna in the same position as the Yagi and aimed it in the same direction. The scan was rerun and produced 25 digital channels. The channels that were missing were ones in the higher range. Channels 50 and higher. We could have done better if our topography was less hilly or we had no houses behind us. Even still, the C2 did a great job when you consider its size and our location.<br><br>
<strong>Conclusion:</strong><br>
<br>If you live within 40 miles of the transmitters or you have a clean line of site and live within 50 miles of your transmitters the C2 antenna is a great way to receive free over the air digital television.<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>July 31, 2008 11:25 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1487
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
					AND entry_id <> 1487
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_298_-_patton_on_blu-ray_and_clearstream_2_uhf_antenna.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
