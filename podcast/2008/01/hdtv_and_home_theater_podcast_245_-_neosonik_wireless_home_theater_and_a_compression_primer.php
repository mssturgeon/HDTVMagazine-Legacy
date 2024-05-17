<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 920";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 920 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, high definition, beach ball, lossy compression, audio video, video, mpeg, MPEG, compression, wireless, Video, DVD, dvd, quality, Wireless, blu, ray, much, looks, Blu, content, audio, mbps, Mbps, digital" />
	<meta name="description" content="This year's CES was as big as ever. Its too big for us to see and talk about everything so we try to stay focused on what most of our listeners are interested in. We recently received an email from Dale in McPherson, KS asking us to look into a product that was not shown on the main floor but in the Venetian hotel. The product is the Neosonik wireless home theater and hi-fi system. We have requested an evaluation system but we thought that the product would make a good topic for discussion.
 
Also,
We've discussed compression in the past, on Episode 13 and then again on Episode 107, but with all the talk about downloading HD content to your Vudu or Apple TV, and how that measures up to HD-DVD or Blu-ray, we thought it might be a good time to revisit the topic. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #245 - Neosonik Wireless Home Theater and a Compression Primer</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #245 - Neosonik Wireless Home Theater and a Compression Primer'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #245 - Neosonik Wireless Home Theater and a Compression Primer</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>January 26, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23245%20-%20Neosonik%20Wireless%20Home%20Theater%20and%20a%20Compression%20Primer&amp;bodytext=This%20year%27s%20CES%20was%20as%20big%20as%20ever.%20Its%20too%20big%20for%20us%20to%20see%20and%20talk%20about%20everything%20so%20we%20try%20to%20stay%20focused%20on%20what%20most%20of%20our%20listeners%20are%20interested%20in.%20We%20recently%20received%20an%20email%20from%20Dale%20in%20McPherson%2C%20KS%20asking%20us%20to%20look%20into%20a%20product%20that%20was%20not%20shown%20on%20the%20main%20floor%20but%20in%20the%20Venetian%20hotel.%20The%20product%20is%20the%20Neosonik%20wireless%20home%20theater%20and%20hi-fi%20system.%20We%20have%20requested%20an%20evaluation%20system%20but%20we%20thought%20that%20the%20product%20would%20make%20a%20good%20topic%20for%20discussion.%0A%20%0AAlso%2C%0AWe%27ve%20discussed%20compression%20in%20the%20past%2C%20on%20Episode%2013%20and%20then%20again%20on%20Episode%20107%2C%20but%20with%20all%20the%20talk%20about%20downloading%20HD%20content%20to%20your%20Vudu%20or%20Apple%20TV%2C%20and%20how%20that%20measures%20up%20to%20HD-DVD%20or%20Blu-ray%2C%20we%20thought%20it%20might%20be%20a%20good%20time%20to%20revisit%20the%20topic.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<strong>Today's Show:</strong><br>

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-01-29.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br></p>

<p><a href="http://neosonik.com/">Neosonik Wireless Home Theater</a><br />
The core of the system of the is the Digital Wireless Controller. You plug in your sources (analog or digital) and the controller transmits the signal to the speakers and the HDTV. This is uncompressed audio and video. The manufacturer claims that all the bits flying through the air stay synchronized.</p>

<p>The signal is broadcast on the 5GHz band and again according to the manufacturer can stream data for two hundred feet through multiple walls. <a href="http://www.cnettv.com/9742-1_53-31979.html">CNET</a> has a video of the system running on their site.</p>

<p><strong>Features</strong><br />
- Full-bandwidth broadcast of all major audio formats<br />
- H.264 wireless video transmission<br />
- Over 200 foot wireless range through walls. Extremely robust. No dropouts.<br />
- Multiple Digital and Analog A/V inputs, including HDMI 1.3<br />
- Wireless audio output. Wired (HDMI 1.3) or Wireless Video output.<br />
- 2nd Zone Stereo playback capability<br />
- Optional HD Radio Digital AM/FM Tuner card<br />
- 17" wide, 3.5" tall, 14" deep<br />
- 21 lbs.</p>

<p>There are two lines of wireless speakers, the Series 6 and Series 4</p>

<p>The final piece of the equation is the Wireless HD Video Receiver. This 3” x 5” x 1” device attaches to any digital TV via Velcro and connects to the HDMI input to receive the High-Definition Video feed wirelessly. It is capable of receiving HD Video streams at distances greater than 200 feet.</p>

<p>Prices are projected to range from $6k to $15k for a complete system (Controller and 5.1 Digital Wireless Loudspeaker package) and is slated for the Summer of 2008.<br />
 <br />
<strong>Compression Primer</strong><br />
As we've said in the past, compression itself is a great thing.  It allows you to store and transfer audio and video content that otherwise would be way too big.  It's when there's too much compression that things go bad.  Yes, in this case there can be too much of a good thing.  Our analogy has always been that of a beach ball.  It's a great device that provides hours of fun for all ages, but in it's fully inflated state, it's very difficult to store or to transport.  You want to be able to deflate (or compress) it to store it in the garage when it isn't in use, or put it in the car to take it to the beach or park, then re-inflate (or uncompress) it when it's time to use it.  It's the same with audio or video content.  You want to compress it to store it on a hard drive or disc media, or to transport it to a set top box or computer, then uncompress it to view it on a TV or computer screen.</p>

<p>There are two basic types of compression, lossless and lossy.  In lossless compression you can compress the content all you want and when you decompress it, it will look exactly like it did originally.  So in this case every time you re-inflate the beach ball, it looks and functions exactly the same.  In lossy compression, when you decompress the content, it doesn't quite look as pristine as it did before it was compressed, you lose some quality.  And the more you compress it, the worse it looks when you decompress it.  In our beach ball analogy, if you take a little of the air out, when you blow it back up it looks almost perfect, but the more air you take out, the less perfect it looks when you blow it back up.  If you compress it down to the smallest you can possibly get, so that it can fit in your back pocket, by the time you blow it back up you're left with a faded, wrinkled mess that barely resembles a beach ball.</p>

<p>So why use lossy compression?  It sounds like a really bad deal.  The fact is that you can compress content to much smaller sizes with lossy compression and still preserve most of the quality.  The trade off between size and quality is much better.  Mainstream lossy compression started with the MPEG1 format back in 1992.  It's the codec (or compression/decompression algorithm) used on Video Discs and Video CDs.  It grew into the MPEG2 codec in 1994, which allowed for much higher quality video and was adopted for use on the DVD, in ATSC video broadcasts and is actually included in the spec for both Blu-ray and HD-DVD.  In fact, many of the early Blu-ray discs were encoded in MPEG2.  Most of the modern video compression schemes have their roots in the next generation of MPEG, MPEG4.  It was finalized in 1998/99 and is rapidly becoming the codec of choice for HDTV via satellite, high definition movie downloads and on the next generation discs.  We have more detail on all three codecs in <a href="http://www.htguys.com/archive/2005/June232005.html">Episode 13</a>.</p>

<p>The three codecs included in both the HD-DVD and Blu-ray specifications are MPEG2, MPEG4 AVC and VC1.  MPEG2 is quickly becoming yesterday's news because MPEG4 allows the same quality playback at about half the file size, so it's a no-brainer.  Early on there were issues with this because of the additional hardware requirements for MPEG4.  As you can imagine, getting the compression that good takes a lot of computation, and a lot of processor horsepower.  With the equipment available on the market today, that complaint has been removed.  MPEG4 AVC (Advanced Video coding), also referred to as H.264, is the technical name for the MPEG4 codec in use in most applications.  VC1 is a Microsoft proprietary alternative to the MPEG4 AVC codec.  The two are very similar, but Microsoft designed VC-1 to reduced the processing power required to decompress video.  The ratio of compression to quality for both codecs tends to be very similar.  Many HD-DVD movies have been encoded in VC-1, Transformers was encoded in MPEG4.</p>

<p>The bottom line is that compression allows us to watch high definition video in our homes.  Without it, a high definition movie would take up terabytes of storage space and would never fit on a Blu-ray or HD-DVD disc.  Forget about trying to broadcast an HDTV show to your house.  Compression is often expressed in terms of bitrate, or how much data (bits) are used to render a second of information non the screen.  Really good high definition broadcasts tend to be in the 18 Mbps range.  When your HDTV content looks really bad, it's because the broadcaster may be compressing the image even more, possibly allowing for only 12, 10 or even 8 Mbps.  You just can't re-inflate the beach ball once you've squeezed it down that small.  Compare that to how good an HD-DVD or Blu-ray movie looks.  Blu-ray can go all the way up to 40 Mbps for video (54 Mbps total for audio and video).  Now that's some high quality viewing.  Then compare that to "HD" downloads to your Apple TV.  The Apple TV supports a max bitrate of 5 Mbps.  Even DVDs allowed for 9.8 Mbps, but then the Apply TV is using MPEG4 while DVD uses MPEG2, so you'll have to draw your own conclusions on that one. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>January 26, 2008 04:40 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 920
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
					AND entry_id <> 920
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_245_-_neosonik_wireless_home_theater_and_a_compression_primer.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
