<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1510";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1510 AND placement_is_primary = 1";
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
	<meta name="keywords" content="high definition, direct usb, memory cards, optical zoom, megapixel resolution, camera, video, high, USB, definition, digital, usb, capture, zoom, optical, images, memory, direct, Direct, cards, Cons, Amazon, mpeg, fps, megapixel" />
	<meta name="description" content="On a recent family vacation to Sequoia National Park, Braden's trusty digital camera finally died.  Sure losing an old gadget is great because you know you get to replace it with the latest and greatest, but it certainly isn't ideal when you're in one of the most scenic places on the planet and happen to be the only tourist with a circa 1990 disposable camera.  Since the newest digital cameras also allow for HD video capture, we thought the quest for a new camera would make nice content for the show." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #305 - Digital Camera Quest</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_305_-_digital_camera_quest';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #305 - Digital Camera Quest'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_305_-_digital_camera_quest.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #305 - Digital Camera Quest</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>August 25, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_305_-_digital_camera_quest.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_305_-_digital_camera_quest.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_305_-_digital_camera_quest.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_305_-_digital_camera_quest.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23305%20-%20Digital%20Camera%20Quest&amp;bodytext=On%20a%20recent%20family%20vacation%20to%20Sequoia%20National%20Park%2C%20Braden%27s%20trusty%20digital%20camera%20finally%20died.%20%20Sure%20losing%20an%20old%20gadget%20is%20great%20because%20you%20know%20you%20get%20to%20replace%20it%20with%20the%20latest%20and%20greatest%2C%20but%20it%20certainly%20isn%27t%20ideal%20when%20you%27re%20in%20one%20of%20the%20most%20scenic%20places%20on%20the%20planet%20and%20happen%20to%20be%20the%20only%20tourist%20with%20a%20circa%201990%20disposable%20camera.%20%20Since%20the%20newest%20digital%20cameras%20also%20allow%20for%20HD%20video%20capture%2C%20we%20thought%20the%20quest%20for%20a%20new%20camera%20would%20make%20nice%20content%20for%20the%20show.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-08-26.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
On a
recent family vacation to Sequoia National Park, Braden's trusty
digital camera finally died.&nbsp; Sure losing an old gadget is great
because you know you get to replace it with the latest and greatest,
but it certainly isn't ideal when you're in one of the most scenic
places on the planet and happen to be the only tourist with a circa
1990 disposable camera.&nbsp; Since the newest digital cameras also allow
for HD video capture, we thought the quest for a new camera would make
nice content for the show.<span></span><br id="zeva1">
<strong id="bueg"><br>
Digital Camera Quest</strong><br id="a9ur0">
Requirements for the new camera:<br id="a9ur1">
<ul id="bueg0">
<li id="bueg1">High Definition video capture</li>
<li id="bueg2">Small</li>

<li id="bueg3">SD memory card</li></ul><br id="ognz1">
"Nice to have" features for the new camera<br id="bueg4">
<ul id="bueg5">
<li id="bueg6">Low cost</li>
<li id="bueg7">Durable</li>
<li id="bueg8">Direct USB connection to computer<br id="hf7p">
</li>
<li id="hf7p0">Charge via USB<br id="bueg9">
</li></ul><br id="hf7p1">
<strong id="l3w7">Options:</strong><br id="hf7p2">

<strong id="slq2"><a title="Panasonic Lumix DMC-TZ5K" target="_blank" href="http://www.htguys.com/shop.php?id=B0011Z8CCG" id="sbve">Panasonic Lumix DMC-TZ5K</a>, $276</strong><br id="d6.q">
<ul id="l3w70">
<li id="l3w71">9-megapixel resolution </li>
<li id="l3w72">10x optical zoom Leica DC lens; Face Detection</li>
<li id="l3w73">Captures images to SD memory cards</li>
<li id="l3w74">720p high-definition motion pictures (<span id="ukab" class="featurebody" style="font-family: verdana,geneva,sans serif; color: black;"><font id="u-np" size="2">1280 x 720 at 30 fps)</font></span></li>
<li id="l3w75">Direct USB interface</li>
<li id="vuyy">4 1/2 stars at Amazon (160 reviews)</li></ul>Cons:<br id="gu420">

<ul id="l3w76">
<li id="l3w77">Uses QuickTime Motion JPEG for video capture rather than MPEG4</li></ul><br id="l3w78">
<strong id="o5.v"><a title="Panasonic Lumix DMC-FX35K" target="_blank" href="http://www.htguys.com/shop.php?id=B0011Z9VX0" id="mzu4">Panasonic Lumix DMC-FX35K</a>, $280</strong><br id="wdbw">
<ul id="yi7t">
<li id="yi7t0">10-megapixel resolution<br id="xt3y">
</li>
<li id="yi7t1">4x wide-angle optical zoom; Mega Optical Image Stabilize</li>
<li id="yi7t2">Captures images to SD memory cards</li>
<li id="yi7t3">720p high-definition motion pictures (<span id="mgf21" class="featurebody" style="font-family: verdana,geneva,sans serif; color: black;"><font id="u-np0" size="2">1280 x 720 at 30 fps)</font></span></li>

<li id="yi7t4">Direct USB interface</li>
<li id="vuyy0">4 1/2 stars at Amazon (12 reviews)</li></ul>Cons:<br id="wdbw5">
<ul id="wdbw6">
<li id="wdbw7">Uses QuickTime Motion JPEG for video capture rather than MPEG4</li></ul><br id="w-a3">
<strong id="bpka"><a title="Samsung NV24HD" target="_blank" href="http://www.htguys.com/shop.php?id=B001283GIM" id="d9yb">Samsung NV24HD</a>, $271</strong><br id="oz7.">
<ul id="vuyy1">
<li id="vuyy2">10-megapixel resolution for high-quality prints; </li>
<li id="vuyy3">3.6x optical zoom; ultra-wide angle 24mm Schneider lens</li>
<li id="vuyy4">Capture high-definition 720p video (1280 x 720 at 30 fps)</li>

<li id="vuyy5">Capture images to SD cards</li>
<li id="vuyy6">Videos recorded to mpeg4 (H.264)</li>
<li id="e.bd">Direct USB interface</li>
<li id="e.bd0">3 1/2 stars at Amazon (16 reviews)</li></ul>Cons:<br id="nq3t">
<ul id="huwz">
<li id="huwz0">Lower user rating at Amazon</li></ul><br id="huwz1">
<strong id="an5r"><a title="Fujifilm FinePix S2000H" target="_blank" href="http://www.htguys.com/shop.php?id=B001D0G56E" id="xbfg">Fujifilm FinePix S2000HD</a>, $299 (pre-order until Sep. 20)</strong><br id="a.ny">
<ul id="an5r0">
<li id="an5r1">10-megapixel resolution</li>

<li id="an5r2">15x optical zoom; wide-angle lens</li>
<li id="an5r3">720p HD compatible video (1280 x 720 at 30 fps)</li>
<li id="an5r4">Capture images to SD/SDHC memory cards</li>
<li id="l83v">Direct USB interface</li>
<li id="l83v0">Records movies in MPEG4 (<abbr id="l83v1" title="International Organization for Standardization">ISO</abbr> standard)</li></ul>Cons:<br id="a.ny4">
<ul id="pwnu">
<li id="pwnu0">Larger form factor</li>

<li id="l83v2">Not out yet, so no user feedback<br id="l83v3">
</li></ul><br id="w-a30">
<strong id="ml3l"><a title="Sanyo Xacti HD700" target="_blank" href="http://www.htguys.com/shop.php?id=B000W9Z0T0" id="ex3e">Sanyo Xacti HD700</a>, $389</strong><br id="m20r">
<ul id="m20r0">
<li id="m20r1">Records high-definition digital video and 7 MP digital still images</li>
<li id="m20r2">5x HD zoom lens; 2.7-inch widescreen LCD</li>
<li id="m20r3">In-camera editing; compatible with iMovie '08</li>
<li id="m20r4">HDMI high-definition output; compatible with SD/SDHC memory cards</li>
<li id="m20r5"> 2 hour and 46 minutes of 720p high-definition video on a single 8GB card</li></ul>Cons:<br id="m20r6">

<ul id="m20r7">
<li id="m20r8">Expensive</li>
<li id="m20r9">More of a video camera than a still camera</li>
<li id="f9-o">USB via docking station<br id="f9-o0">
</li></ul><br id="g-7r">
Know of a good option for a digital camera that records HD video?&nbsp; Let us know!<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>August 25, 2008 10:58 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1510
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
					AND entry_id <> 1510
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/08/hdtv_and_home_theater_podcast_305_-_digital_camera_quest.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
