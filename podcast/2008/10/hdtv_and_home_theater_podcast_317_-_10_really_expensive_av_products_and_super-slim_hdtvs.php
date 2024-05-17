<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1507";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1507 AND placement_is_primary = 1";
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
	<meta name="keywords" content="slim hdtvs, street price, super slim, flat panel, picture quality, color, hdtvs, HDTVs, LCD, OLED, technology, Sony, lcd, oled, panel, sony, inch, outstanding, Samsung, HDTV, content, series, bravia, flat, XEL" />
	<meta name="description" content="Amazon has an excellent write-up on four super slim HDTVs.  They offer a glimpse into the future of what television may be like in a few years.  Imagine what the family room or the home theater will look like when wireless HDMI removes the need to tether your TV to a set top box or a Bu-ray player.  And it also removes the need to have your speakers wired to your receiver or amplifier.  Couple that with a giant 60, 70 or even 80-inch flat panel TV only millimeters thick, mounted directly to the wall.  Now we're talking Jetson's technology.  And by the looks of things, it may not be that far off." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #317 - 10 Really Expensive A/V Products and Super-Slim HDTVs</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #317 - 10 Really Expensive A/V Products and Super-Slim HDTVs'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #317 - 10 Really Expensive A/V Products and Super-Slim HDTVs</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October  6, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23317%20-%2010%20Really%20Expensive%20A%2FV%20Products%20and%20Super-Slim%20HDTVs&amp;bodytext=Amazon%20has%20an%20excellent%20write-up%20on%20four%20super%20slim%20HDTVs.%20%20They%20offer%20a%20glimpse%20into%20the%20future%20of%20what%20television%20may%20be%20like%20in%20a%20few%20years.%20%20Imagine%20what%20the%20family%20room%20or%20the%20home%20theater%20will%20look%20like%20when%20wireless%20HDMI%20removes%20the%20need%20to%20tether%20your%20TV%20to%20a%20set%20top%20box%20or%20a%20Bu-ray%20player.%20%20And%20it%20also%20removes%20the%20need%20to%20have%20your%20speakers%20wired%20to%20your%20receiver%20or%20amplifier.%20%20Couple%20that%20with%20a%20giant%2060%2C%2070%20or%20even%2080-inch%20flat%20panel%20TV%20only%20millimeters%20thick%2C%20mounted%20directly%20to%20the%20wall.%20%20Now%20we%27re%20talking%20Jetson%27s%20technology.%20%20And%20by%20the%20looks%20of%20things%2C%20it%20may%20not%20be%20that%20far%20off.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-07.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Amazon has an excellent write-up on four super slim HDTVs.&nbsp; They offer a glimpse into the future of what television may be like in a few years.  Imagine what the family room or the home theater will look like when wireless HDMI removes the need to tether your TV to a set top box or a Bu-ray player.  And it also removes the need to have your speakers wired to your receiver or amplifier.  Couple that with a giant 60, 70 or even 80-inch flat panel TV only millimeters thick, mounted directly to the wall.  Now we're talking Jetson's technology. And by the looks of things, it may not be that far off.<br>

<strong><br>
Super-Slim HDTVs</strong><br>
<em>From Amazon:</em><br>
New technologies are making
flat-panel HDTVs slimmer and brighter than ever before. From LG's
smooth frameless "Edge" plasma to Samsung's 2-inch deep 850 LCD series
to Sony's revolutionary OLED technology, here are some of the slimmest
and brightest.<br>
<br><strong>Sony XEL-1 11" OLED HDTV</strong> (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00126W14O" id="f75m">Buy now</a>)<br>
Sony’s
industry first OLED (Organic Light Emitting Diode) TV, the XEL-1, has a
3 millimeter thin panel and offers unparalleled picture quality with
amazing Contrast, outstanding Brightness, exceptional color
reproduction, and a rapid Response Time. The XEL-1 delivers outstanding
performance in key picture quality categories.<br>
<br>OLED technology
can completely turn off Pixels when reproducing black, resulting in
more outstanding dark scene detail and a Contrast Ratio of 1,000,000:1.
OLED creates unmatched color expression and detail and enables rapid
response times for smooth and natural reproduction of fast moving
content such as sports. The XEL-1 features the latest connectivity
options including two HDMI inputs, a Digital tuner, and a Memory Stick
slot for viewing high-Resolution photos.<br>

<br>Street price: $2,499 USD<br>
<br><strong>LG "Edge" Frameless 1080p Plasma HDTVs</strong><br>
LG
wraps their latest Full HD 1080p ultra-slim 3.1-inch plasma panel in a
sophisticated single layer design. Hidden speakers enhance the clean,
contemporary look. This display is also THX Certified, providing
outstanding picture quality. The <a title="LG 50PG60" target="_blank" href="http://www.htguys.com/shop.php?id=B001A75V3I" id="kj9v">LG 50PG60</a>
plasma HDTV was awarded the "Best of Innovations" award at the 2008
International Consumer Electronics Show for its cutting-edge display
technologies and seamless look.<br>
<br>Street price: $2,088<br>
Also available, 60" <a title="60PG60" target="_blank" href="http://www.htguys.com/shop.php?id=B0019TVYMY" id="upc4">60PG60</a> for $3,239<br>
<br><strong>Samsung LN52A850 52" 1080p LCD HDTV with RED Touch of Color</strong> (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001DW0EWI" id="d7xl">Buy now</a>)<br>

At
only 1.9 inches thick, Samsung's striking new 850 series offers all of
the features of the 750 Series models in an incredibly thin, sleek
package. The Series 8 1080p LCD HDTVs have all of the features you'd
expect from a top-of-the-line Samsung - superb image, a blazing-fast
4ms response time, 120Hz refresh rate with Automotion plus image
interpolation technology for smooth fast motion, and rich
interconnectivity, all wrapped in the stylish "TOC" color-infused
bezel. Enjoy MPEG and JPEG files stored on external devices by
connecting through a side-mounted USB 2.0, turning their LCD into a
full-scale home viewing gallery, or connect their MP3 players for a
dynamic audio experience.<br>
<br>Includes built-in DLNA<br>
<br>Street price: $2,569<br>
<strong><br>
Sony Bravia KLV-40ZX1M</strong><br>
Sony
is also introducing the 40-inch diagonal 1080p KLV-40ZX1M flat panel
LCD monitor. The revolutionary display measures just approximately
9.9mm deep--about the depth of a Blu-ray disc jewel case--and weighs
just 26 pounds.<br>
<br>The 40ZX1M features an edge-lit wide color gamut
LED backlight delivering exceptional color reproduction and detailed
contrast and Sony’s Motionflow 120 Hz technology for clarity and
resolution when viewing film or video content with motion, and BRAVIA
Engine 2 video processing.<br>
<br>The monitor will have just one HDMI
port, so an A/V receiver will be required to provide ports for
connecting multiple content sources. However. compatibility with Sony’s
BRAVIA Wireless Link allowing users to stream high content wirelessly
to the monitor from high-definition sources such as a set-top cable or
satellite tuner or Blu-ray Disc player.<br>
<br>Sony is producing a matching flat speaker bar with four full-range speakers and two tweeters and a wall-hugging bracket.<br>

<br>The BRAVIA KLV-40ZX1M is expected to ship in December 2008.
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October  6, 2008 11:14 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1507
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
					AND entry_id <> 1507
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_317_-_10_really_expensive_av_products_and_super-slim_hdtvs.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
