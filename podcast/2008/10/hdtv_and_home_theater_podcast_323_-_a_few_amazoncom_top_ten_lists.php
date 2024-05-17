<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1534";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1534 AND placement_is_primary = 1";
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
	<meta name="keywords" content="stars details, lcd hdtv, blu ray, details samsung, player stars, details, Details, stars, Inch, DVD, inch, HDTV, dvd, hdtv, LCD, Samsung, lcd, samsung, player, blu, ray, Blu, Player, players, top" />
	<meta name="description" content="We've compile a few lists of some of the top selling items at Amazon.com.  A few of the lists shocked us a little bit.  Of course, we have an opinion  on what they mean, and what we might see for the Holiday shopping season." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #323 - A few amazon.com top ten lists</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October 27, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23323%20-%20A%20few%20amazon.com%20top%20ten%20lists&amp;bodytext=We%27ve%20compile%20a%20few%20lists%20of%20some%20of%20the%20top%20selling%20items%20at%20Amazon.com.%20%20A%20few%20of%20the%20lists%20shocked%20us%20a%20little%20bit.%20%20Of%20course%2C%20we%20have%20an%20opinion%20%20on%20what%20they%20mean%2C%20and%20what%20we%20might%20see%20for%20the%20Holiday%20shopping%20season.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-28.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
We've compile a few lists
of some of the top selling items at Amazon.com.&nbsp; A few of the lists
shocked us a little bit.&nbsp; Of course, we have an opinion&nbsp; on what they
mean, and what we might see for the Holiday shopping season.<br>

<br><strong>A Few Amazon.com Top Ten lists</strong><br>
<br><strong>Televisions</strong><br>
10. Toshiba 19LV505 19-Inch 720p LCD HDTV with Built In DVD Player<br>
&nbsp;&nbsp;&nbsp; 3.5 / 5 stars, $334, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001413EQ6" id="ulzp">Details</a><br>
<br>9. Samsung LN52A750 52-Inch 1080p DLNA LCD HDTV with RED Touch of Color<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $2123, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001418WF4" id="ulzp">Details</a><br>
<br>8. Samsung LN19A450 19-Inch 720p LCD HDTV<br>

&nbsp;&nbsp;&nbsp; 4 / 5 stars, $320, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001418WG8" id="ulzp">Details</a><br>
<br>7. Samsung LN22A450 22-Inch 720p LCD HDTV, Black<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $385, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00141AZCW" id="ulzp">Details</a><br>
<br>6. Samsung LN40A650 40-Inch 1080p 120Hz LCD HDTV with RED Touch of Color<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $1298, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0014175NE" id="ulzp">Details</a><br>
<br>

5. Samsung LN46A550 46-Inch 1080p LCD HDTV<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $1289, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0014175E8" id="ulzp">Details</a><br>
<br>4. Samsung LN40A550 40-Inch 1080p LCD HDTV<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $998, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001418W2C" id="ulzp">Details</a><br>
<br>3. Samsung LN32A450 32-Inch 720p LCD HDTV<br>
&nbsp;&nbsp;&nbsp; 5 / 5 stars, $623, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00141AYIC" id="ulzp">Details</a><br>

<br>2. Samsung LN46A650 46-Inch 1080p 120Hz LCD HDTV with RED Touch of Color<br>
&nbsp;&nbsp;&nbsp; 5 / 5 stars, $1570, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001413D94" id="ulzp">Details</a><br>
<br>1. Samsung LN52A650 52-Inch 1080p 120Hz LCD HDTV with Red Touch of Color<br>
&nbsp;&nbsp;&nbsp; 5 / 5 stars, $2029, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001413DF8" id="ulzp">Details</a><br>
<br>&nbsp;&nbsp;&nbsp; <br>
<strong>Of note:</strong><br>
<ul>

<li>Nine of the top 10 TVs are made by Samsung</li>
<li>All of the TVs are High Definition (60/40 split, 1080p to 720p)</li>
<li>All of the TVs are LCD</li>
<ul>
<li>The first plasma on the list is</li>
<ul>
<li>#17: Panasonic Viera TH-42PZ85U 42-Inch 1080p Plasma HDTV<br>
</li></ul>
<li>The first CRT, also the first SDTV on the list is</li>
<ul>
<li>#85: Haier HTR20 20" CRT TV<br>

</li></ul>
<li>The first rear projection on the list is</li>
<ul>
<li>#86: Samsung HL56A650 56-Inch 1080p Slim DLP HDTV</li></ul></ul></ul><br>
<strong>Our conclusions:</strong><br>
<ul>
<li>Watch for significant price drops in plasma and rear projection this Holiday season as they try to keep a toe hold in the market</li>
<li>While the LCD market looks very competitive, prices should remain more or less stable with demand being so high</li>
<li>CRT is dead</li>
<li>Rear project is just about dead</li>

<li>Plasma needs ultra-contrast, fast! (and affordable!)<br>
</li></ul><br>
<br><strong>DVD players<br>
</strong>10. Toshiba DVR610 1080p Upconverting Tunerless VHS DVD Recorder<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $158, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001415ERS" id="ulzp">Details</a><br>
<br>9. Philips DVP5140 Multiformat DVD Player with DivX, MP3, Windows Media Support<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $48, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000F2KUK8" id="p6gd">Details</a><br>

<br>
8. Philips Hard Disk/DVD Recorder 160 GB (DVDR3576H/37)<br>
&nbsp;&nbsp;&nbsp; 4 / 5 starts, $295, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0013WM0BQ" id="mln2">Details</a> <br>
<br>
7. Sony DVP-NS700H/B 1080p Upscaling DVD Player<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $73, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0015VW3BM" id="szbw">Details</a><br>
<br>
6. Panasonic DMP-BD35K 1080p Blu-Ray Player<br>

&nbsp;&nbsp;&nbsp; Not rated, $289, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001GAOYCS" id="urt9">Details</a><br>
<br>
5. Coby DVD-224 Compact DVD Player<br>
&nbsp;&nbsp;&nbsp; 3 / 5 stars, $28, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000261N6M" id="omg1">Details</a><br>
<br>
4. Sony DVP-FX820 8-Inch Portable DVD Player<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $154, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B00139R1TA" id="omg1">Details</a><br>

<br>
3. Sony BDP-S550 1080p Blu-Ray Player<br>
&nbsp;&nbsp;&nbsp; 4.5 / 5 stars, $329, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001FN3ZRQ" id="omg1">Buy now</a><br>
<br>
2. Samsung BD-P1500 1080p Blu-ray Player<br>
&nbsp;&nbsp;&nbsp; 4 / 5 stars, $215, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B0014H16V0" id="omg1">Details</a><br>
<br>
1. Sony BDP-S350 1080p Blu-ray Disc Player<br>

&nbsp;&nbsp; &nbsp;4.5 / 5 stars, $264, <a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001A4LVYY" id="omg1">Details</a><br>
<br>&nbsp;&nbsp; <br>
<strong>Of note:</strong><br>
<ul>
<li>The top 3 DVD players are Blu-ray players, 4 of the top 10 are Blu-ray</li>
<li>Sony has 4 players on the list</li>
<li>Of the non-Blu-ray players, none is just a "standard DVD" player</li>
<ul>
<li>One is a portable player</li>

<li>One is ultra compact, and ultra inexpensive</li>
<li>Two are upscaling/upconverting models</li>
<li>One is a DVR and recorder</li>
<li>One is a VHS and recorder (also an upconverter)<br>
</li>
<li>One supports digital media playback (DivX, MP3, etc.)<br>
</li></ul></ul><br>
<strong>Our conculsions:</strong><br>
<ul>
<li>Blu-ray
is gaining momentum.&nbsp; We still think Blu-ray player prices have some
room to drop, so look for them to be aggressive in "unseating" DVD
players this Holiday season</li>

<li>At around $75, upconverting players
still make sense to a lot of people.&nbsp; When Blu-ray hits $150 it will
take off, when it gets below $100 it will be pretty much the the only
format available.&nbsp; DVD will be the next VHS at that point.<br>
<br></li></ul><br>

		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October 27, 2008 10:17 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1534
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
					AND entry_id <> 1534
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_323_-_a_few_amazoncom_top_ten_lists.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
