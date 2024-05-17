<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1353";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1353 AND placement_is_primary = 1";
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
	<meta name="keywords" content="accepts signals, lcd hdtv, composite video, video accepts, inputs including, video, star, signals, accepts, inch, HDTV, digital, hdtv, LCD, buy, ntsc, reviews, Amazon, including, component, amazon, lcd, composite, Buy, inputs" />
	<meta name="description" content="First of all, you can now follow us at &lt;a target=&quot;blank&quot; href=&quot;http://twitter.com/hdtvpodcast&quot;&gt;Twitter&lt;/a&gt;.  Check it out and tell us what you think.  There's a fun article about &lt;a target=&quot;blank&quot; href=&quot;http://www.electronichouse.com/article/great_movie_scenes_for_surround_sound/C155&quot;&gt;Great Movie Scenes for Surround Sound&lt;/a&gt; at Electronic House and it seemed like something cool to discuss on the show.  And we've compiled a list of six 32&quot; HDTVs for budget conscious buyers. " />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #268 - Budget 32-inch LCDs</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #268 - Budget 32-inch LCDs'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #268 - Budget 32-inch LCDs</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April 17, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23268%20-%20Budget%2032-inch%20LCDs&amp;bodytext=First%20of%20all%2C%20you%20can%20now%20follow%20us%20at%20%3Ca%20target%3D%22blank%22%20href%3D%22http%3A%2F%2Ftwitter.com%2Fhdtvpodcast%22%3ETwitter%3C%2Fa%3E.%20%20Check%20it%20out%20and%20tell%20us%20what%20you%20think.%20%20There%27s%20a%20fun%20article%20about%20%3Ca%20target%3D%22blank%22%20href%3D%22http%3A%2F%2Fwww.electronichouse.com%2Farticle%2Fgreat_movie_scenes_for_surround_sound%2FC155%22%3EGreat%20Movie%20Scenes%20for%20Surround%20Sound%3C%2Fa%3E%20at%20Electronic%20House%20and%20it%20seemed%20like%20something%20cool%20to%20discuss%20on%20the%20show.%20%20And%20we%27ve%20compiled%20a%20list%20of%20six%2032%22%20HDTVs%20for%20budget%20conscious%20buyers.%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-18.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
First of all, you can now follow us at <a target="blank" href="http://twitter.com/hdtvpodcast">Twitter</a>.  Check it out and tell us what you think.  There's a fun article about <a target="blank" href="http://www.electronichouse.com/article/great_movie_scenes_for_surround_sound/C155">Great Movie Scenes for Surround Sound</a> at Electronic House and it seemed like something cool to discuss on the show.  And we've compiled a list of six 32" HDTVs for budget conscious buyers. </p>

<p><strong>Budget 32 inch LCDs for the Dorm Room or Den for around $700</strong><br><br />
<em>Sharp Aquos LC32D43U 32-inch 720p LCD HDTV</em> ($760 <a target="blank" href="http://www.htguys.com/shop.php?id=B000MKUQBG">Buy Now</a>)<ul><li>720p (1366 X 768)</li><li>6000:1 Dynamic Contrast Ratio</li><li>6ms response time.</li><li>Viewing Angles (176°H x 176°W)</li><li>High Brightness (450 cd/m2)</li><li>Built-in ATSC / QAM / NTSC Tuners provide access to DTV and analog TV channels.</li><li>5 A/V inputs, including:</li><ul><li>2 composite video</li><li>1 S-video</li><li>2 component video (accepts signals up to 1080i)</li><li>2 HDMI v1.2 (accepts signals up to 1080i)</li></ul></ul>Out of 35 reviews at Amazon.com it received a 4.5 star rating out of a possible 5. One three star reviewer had an issue with the buttons locking up. Sharp was blaming the Cable company as they had received over a hundred calls on the subject. Another three star rating resulted in audio levels with DVDs. HD sounded OK. Sounds more like a set up problem to us. The last three star review was due to some dim pixels on the set that Sharp says is within warranty.<br><br />
 </p>

<p><em>Sony Bravia KDL-32M3000 32-inch 720p LCD HDTV</em> ($662 <a target="blank" href="http://www.htguys.com/shop.php?id=B000UN3GXA">Buy Now</a>)<ul><li>1366 x 768 pixels</li><li>8-millisecond pixel response time</li><li>1000:1 dynamic contrast ratio</li><li>178°(H) x 178°(V) viewing angle</li><li>built-in digital (ATSC) and analog (NTSC) tuners</li><li>coaxial digital audio output for Dolby® Digital</li><li>7 A/V inputs, including:</li><ul><li>3 composite video (2 rear, 1 side)</li><li>1 S-video</li><li>2 component video (accepts signals up to 1080i)</li><li>2 HDMI (accepts signals up to 1080i)</li></ul></ul>Out of 8 reviews at Amazon.com it received 4.5 stars. The one star review was due to the reviewers dislike for the analog picture on the TV.<br><br />
 </p>

<p><em>Toshiba 32AV500U 32-inch 720p LCD HDTV</em> ($680 <a target="blank" href="http://www.htguys.com/shop.php?id=B00140S232">Buy Now</a>)<ul><li>built-in digital (ATSC) and analog (NTSC) tuners</li><li>8-millisecond pixel response time</li><li>176°(H) x 176°(V) viewing angle</li><li>DynaLight™ dynamic backlight control for improved contrast and black level</li><li>6 A/V inputs, including:</li><ul><li>2 composite video (1 rear, 1 side)</li><li>1 S-video</li><li>2 component video (accepts signals up to 1080i)</li><li>2 HDMI (accepts signals up to 1080i)</li></ul></ul>Four star rating out of four reviews at Amazon.com<br><br />
 </p>

<p><em>Olevia 532H 32-Inch LCD HDTV</em> ($657 <a target="blank" href="http://www.htguys.com/shop.php?id=B000H2HVLC">Buy Now</a>)<ul><li>720p 1366 x 768 Pixels</li><li>178°/178° Viewing Angle</li><li>8 ms Response Time</li><li>built-in digital (ATSC) and analog (NTSC) tuners</li><li>5 A/V inputs, including:</li><ul><li>1 composite video</li><li>1 VGA</li><li>2 component video (accepts signals up to 1080i)</li><li>1 HDMI</li></ul></ul>Four and a half stars out of five with 52 customer reviews at Amazon.com There were six ratings of three star or less. Those mainly complained about defective units shortly after purchase. Some complained about the time it took to get the unit repaired and returned.<br><br />
 </p>

<p><em>Westinghouse SK-32H240S 32" LCD HDTV</em> ($647 <a target="blank" href="http://www.htguys.com/shop.php?id=B000NIK9WS">Buy Now</a>)<ul><li>720p 1366 x 768 Pixels</li><li>1200:1 Contrast Ratio</li><li>built-in digital (ATSC), QAM, and (NTSC) tuners</li><li>SPDIF Optical Out</li><li>7 A/V inputs, including:</li><ul><li>1 composite video</li><li>1 S-video</li><li>2 component video (accepts signals up to 1080i)</li><li>2 HDMI (accepts signals up to 1080i)</li><li>1 VGA</li></ul></ul>Four stars out of five from Amazon.com customers. There were three one star ratings. Two reviewers said the TV died after about six months. The other one star review didn't like the picture and bought a Panasonic instead.<br></p>

<p><br />
<em>Vizio VW32L 32 inch LCD HDTV</em> ($650 <a target="blank" href="http://www.htguys.com/shop.php?id=B0015EUO56">Buy Now</a>)<ul><li>720p 1366 x 768 Pixels</li><li>8 ms Response Time</li><li>built-in digital (ATSC) and analog (NTSC) tuners</li><li>6 A/V inputs, including:</li><ul><li>1 composite video (1 rear, 1 side)</li><li>1 VGA</li><li>2 component video (accepts signals up to 1080i)</li><li>2 HDMI</li></ul></ul>No reviews at Amazon at this time. A previous model received an average of four stars based on 19 customers. There were (3) three star reviews , (1) two star and (1) one star review for the previous model.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April 17, 2008 11:34 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1353
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
					AND entry_id <> 1353
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_268_-_budget_32-inch_lcds.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
