<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1512";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1512 AND placement_is_primary = 1";
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
	<meta name="keywords" content="mars fox, life mars, american idol, weekend update, shows fall, nbc, NBC, fox, shows, fall, Fox, Fall, CBS, new, cbs, ABC, abc, Kitchen, kitchen, Worst, life, New, Life, preview, mars" />
	<meta name="description" content="Each year since we've been doing this show we tale a look at the Fall TV season and try to pick shows that we'd like to watch. Back in 2006 when we first starting doing this, a good number of shows were in SD. Today pretty much all of them are in HD with the exception of a few reality shows." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #306 - Fall TV Preview</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_306_-_fall_tv_preview';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #306 - Fall TV Preview'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_306_-_fall_tv_preview.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #306 - Fall TV Preview</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>August 28, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_306_-_fall_tv_preview.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_306_-_fall_tv_preview.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_306_-_fall_tv_preview.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_306_-_fall_tv_preview.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23306%20-%20Fall%20TV%20Preview&amp;bodytext=Each%20year%20since%20we%27ve%20been%20doing%20this%20show%20we%20tale%20a%20look%20at%20the%20Fall%20TV%20season%20and%20try%20to%20pick%20shows%20that%20we%27d%20like%20to%20watch.%20Back%20in%202006%20when%20we%20first%20starting%20doing%20this%2C%20a%20good%20number%20of%20shows%20were%20in%20SD.%20Today%20pretty%20much%20all%20of%20them%20are%20in%20HD%20with%20the%20exception%20of%20a%20few%20reality%20shows.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-08-29.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Each
year since we've been doing this show we tale a look at the Fall TV
season and try to pick shows that we'd like to watch. Back in 2006 when
we first starting doing this, a good number of shows were in SD. Today
pretty much all of them are in HD with the exception of a few reality
shows.<br>
<br><strong>Fall TV Preview</strong><br>
<br><a href="http://www.cbs.com/primetime/fall_preview_2008/schedule.php" target="_blank" title="CBS:">CBS:</a><br>

Worst Week, The Mentalist, Gary Unmarried, Eleventh Hour, The Ex List<br>
<br><a href="http://www.nbc.com/Primetime/" target="_blank" title="NBC:">NBC:</a><br>
America's Toughest Jobs, My Own Worst Enemy, Knight Rider, Kath and Kim, SNL Weekend Update Thursday, Crusoe<br>
<br><a href="http://abc.go.com/#navTabId=fall" target="_blank" title="ABC:">ABC:</a><br>
Opportunity Knocks, Life on Mars<br>
<br><a href="http://www.fox.com/programming/" target="_blank" title="Fox:">FOX:</a><br>
Fringe, Do Not Disturb<br>
<br><a href="http://www.cwtv.com/thecw/the-cw-fall-2008-preview" target="_blank" title="CW:">CW:</a><br>
90210, Privileged, Stylista, In Harm's Way, Surviving Suburbia, Valentine, Easy Money<br>

<br><strong id="p4rj"></strong>
<strong id="p4rj"><br>
Braden's Picks: </strong><br id="p4rj0">
<em id="p4rj1">New: </em><br id="p4rj2">
<ul id="p4rj3">
<li id="p4rj4">NBC: Knight Rider, Crusoe, SNL Weekend Update (all 3 of them)</li>
<li id="p4rj5">ABC: Life on Mars</li>
<li id="p4rj6">Fox: Do Not Disturb</li></ul><em id="p4rj7">Possible:</em><br id="p4rj8">
<ul id="p4rj9">
<li id="p4rj10">CBS: The Mentalist</li>

<li id="p4rj11">NBC: My Own Worst Enemy</li></ul><em id="p4rj12">Old:</em><br id="p4rj13">
<ul id="p4rj14">
<li id="p4rj15">CBS: NCIS, Without a Trace, The Unit, Numb3rs</li>
<li id="p4rj16">NBC: Chuck, The Office, 30 Rock, ER</li>
<li id="p4rj17">Fox: Terminator: Sarah Connor Chronicles, 24, Hell's Kitchen, Kitchen Nightmares</li></ul> <br id="p4rj18">
<strong id="p4rj19">Ara's Picks: </strong><br id="p4rj20">
<em id="p4rj21">New: </em><br id="p4rj22">
<ul id="p4rj23">
<li id="p4rj24">ABC: Life on Mars</li>

<li id="p4rj25">Fox: Fringe</li></ul><em id="p4rj26">Old: </em><br id="p4rj27">
<ul id="p4rj28">
<li id="p4rj29">ABC: Dancing with the Stars, Lost, Pushing Daises, According to Jim</li>
<li id="p4rj30">CBS:
CSI NY and Miami, Ghost Whisperer, NCIS, The Unit, Numb3rs, Two and a
Half Men, How I met your Mother, Big Bang Theory, Rules of Engagement,
Survivor</li>
<li id="p4rj31">NBC: Chuck, 30 Rock, Deal or No Deal, Heroes</li>
<li id="p4rj32">Fox: Terminator:  Sarah Connor Chronicles, Bones, House, 24, Hell's Kitchen, Kitchen Nightmares, American Idol</li></ul><br id="p4rj33">
<strong id="a217">Midseason Replacements </strong><br id="a2170">
ABC - Lost, According to Jim, Scrubs (formerly of NBC) and The Bachelor.<br id="p4rj34">
<br id="p4rj35">CBS - Rules of Engagement and Harper's Island.<br id="p4rj36">

<br id="p4rj37">The CW - Reaper<br id="p4rj38">
<br id="p4rj39">Fox - 24, American Idol and Hell's Kitchen,<br id="p4rj40">
<br id="p4rj41">NBC - Law &amp; Order, Friday Night Lights, Medium, Merlin, and Kings<br id="p4rj45">
<br id="p4rj46">TV Guide has a nice grid with all the new and returning shows: <a href="http://www.tvguide.com/special/fall-preview-2008/fall-schedule.aspx" target="_blank" title="FALL TV PREVIEW">Fall TV Preview</a><br>
Yahoo! has short descriptions for some of the new shows: <a title="New Fall Shows" target="_blank" href="http://www.tv.yahoo.com/collections/8598" id="prii">New Fall Shows</a><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>August 28, 2008 10:45 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1512
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
					AND entry_id <> 1512
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_306_-_fall_tv_preview.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
