<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1557";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1557 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, lcd hdtv, ray player, inch lcd, circuit city, LCD, HDTV, lcd, hdtv, ray, blu, Blu, inch, player, Samsung, samsung, plasma, Plasma, true, True, circuit, City, city, sony, Circuit" />
	<meta name="description" content="On today's show we will discuss the expected Black Friday deals we can all look forward to on the day after Thanksgiving. We will also see how our predictions we made back in September turned out." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #330 - Black Friday Roundup</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_330_-_black_friday_roundup';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #330 - Black Friday Roundup'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_330_-_black_friday_roundup.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #330 - Black Friday Roundup</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>November 20, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_330_-_black_friday_roundup.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_330_-_black_friday_roundup.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_330_-_black_friday_roundup.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_330_-_black_friday_roundup.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23330%20-%20Black%20Friday%20Roundup&amp;bodytext=On%20today%27s%20show%20we%20will%20discuss%20the%20expected%20Black%20Friday%20deals%20we%20can%20all%20look%20forward%20to%20on%20the%20day%20after%20Thanksgiving.%20We%20will%20also%20see%20how%20our%20predictions%20we%20made%20back%20in%20September%20turned%20out.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-11-21.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
On today's show we will discuss the expected Black Friday deals we can all look forward to on 
the day after Thanksgiving. We will also see how our predictions we made back in September 
turned out.<br><br>
<strong>Black Friday Roundup</strong><br><br>
<strong>Best Buy</strong><br>
<ul>
<li>80 Gig Playstation 3 package. comes with free Ratchet and Clank Future: Tools of destruction game and Casino royale blue ray DVD for 399.99 Min 16 per store</li>
<li>Panosonic Viera 50" 720p Plasma HDTV 899.99 min 10 per store</li>
<li>Dynex 32 in LCD 720p $400 min 20 per store</li>
<li>Dynex 42 inch 720p Plasma $599</li>
<li>LG 32 inch LCD (includes TV & Video setup) $699 min 4 per store</li>
<li>Samsung 42 inch Plasma $699</li>
<li>Mitsubishi 60" Class 1080P DLP HDTV - $999.99</li>
<li>SONY BDP-S350 $249.99 min 5 per store</li>
<li>Blu Ray Discs $16.99 (Kung Fu Panda, Indiana Jones, I am Legend, and More) minimum of 5 each)</li>
<li>DVDs $4.99 minimum of 10 each</li>
</ul><br>
<strong>Circuit City</strong><br>
<ul>
<li>Mitsubishi 65" Home Theater 1080p DLP HDTV (Model # WD65735) - $1,199.99</li>
<li>Sony 40" 1080p LCD HDTV & Sony Blu-Ray Player Bundle (Model # KDL40V4100 and BDPS350) - $1,199.98</li>
<li>Samsung 42" 720p Plasma HDTV (Model # PN42A400) - $699.99</li>
<li>Samsung 32" 720p LCD HDTV (Model # LN32A300) - $499.99</li>
<li>Samsung Blu-Ray Disc Player BDP1500 - $199.99</li>
<li>Onkyo 7.1 Home Theater Receiver w/ Energy 5.1 Speaker System - $599.99</li>
</ul><br>
<strong>Wal*Mart</strong><br>
<ul>
<li>Magnavox Blu Ray Player $128</li>
<li>Samsung Plasma 50 inch 720p $798</li>
<li>Xbox 360 + Guitar Hero III & wireless guitar pack $199</li>
<li>Toshiba 32" Regza 1080P LCD HDTV & Playstation 3 Blu-ray Value Bundle $997</li>
</ul><br>
<strong>Costco</strong><br>
<ul>
<li>Sharp 32" 720p HDTV LCD - $499.99</li>
<li>Sharp 42" 1080p LCD HDTV - $200 Off</li>
<li>Sharp Aquos 52" 1080p LCD HDTV - $300 Off</li>
<li>Sharp Aquos 65" 1080p LCD HDTV - $300 Off</li>
<li>Vizio 37" 720p LCD HDTV - $100 Off</li>
<li>iTunes $60 Card Pack (4 x $15 Cards) - $49.99</li>
</ul><br>
<strong>Radio Shack</strong><br>
<ul>
<li>Memorex DVD player with 1080p upconversion -- $29.99</li>
<li>Samsung Blu-ray player -- $199.99</li>
<li>AOC 32-inch 720p LCD HDTV -- $399.99</li>
<li>AOC Emerson 19-inch LCD HDTV -- $249.99</li>
<li>XBox 360 Bundle w/Controller, Headset and A/V Cable -- $249.99</li>
</ul><br>
<strong>Dell</strong><br>
<ul>
<li>Sony Blu-ray player -- $249</li>
<li>Sony Home Theater System -- $229</li>
<li>Sharp 42-Inch 1080p LCD HDTV -- $779</li>
<li>XBox Arcade Bundle with Rock Band 2 Game -- $199</li>
</ul><br>
<strong>Kmart</strong><br>
<ul>
<li>Sony Blu-ray Player (BDP-S350) -- $179 (with a firmware upgrade and a 1 GB USB stick this will be come BD Live Compliant)</li>
<li>Element 32-Inch LCD HDTV -- $399</li>
<li>Memorex 26-Inch Combo LCD HDTV/DVD -- $399</li>
<li>Samsung 42-Inch 720p Plasma HDTV (PN42A400) -- $699</li>
<li>Sylvania 20-Inch LCD HDTV/DVD Combo -- $249</li>
</ul><br><br>
<em>Ara:</em>
<ul>
    <li>HDTVs</li>
	<ul>
      <li>Target and Wal*Mart will have a tier one (SONY, Panasonic, Sharp) 32 inch LCD for less than $500. <strong>False but for partial credit Circuit City has a Samsung 32 inch LCD for $499</strong></li>
      <li>BestBuy and Circuit City will have a 42 inch Plasma for $750. <strong>True</strong></li>
      <li>BestBuy and Circuit City will give away a Blu-ray Player with the purchase of a 60 inch Plasma. <strong>Almost True Circuit City Free Blu Ray with 40 inch LCD</strong></li>
      <li>No one will have a door buster for a rear projection TV. <strong>False</strong></li>
	</ul>
    <li>Blu-ray</li>
	<ul>
      <li>Wal*Mart will have a no name Blu-ray player for $150. <strong>True</strong></li>
      <li>You will be able to find a name brand Blu-ray player that supports the full Blu-ray specification for $200. <strong>True</strong></li>
      <li>Target will have a buy one get one free deal on Blu-ray Movies. <strong>False, but great deals do exist everywhere</strong></li>
    </ul>
	<li>Receivers</li>
	<ul>
      <li>The Onkyo 606 will be found for less that $300 at Circuit City</li>
    </ul>
	<li>Remote Control</li>
	<ul>
      <li>The Harmony One will go for $150 (Online Only)</li>
      <li>The Harmony 880 will go for $50 after Mail in rebate (Online Only)</li>
    </ul>
</ul><br>
<em>Braden:</em>
<ul>
    <li>HDTVs</li>
    <ul>
        <li>42" plasma for less than $600. <strong>True</strong></li>
        <li>40" LCD for less than $600. <strong>False, the closest we found was an online deal for $700</strong></li>
        <li>60" or greater DLP for less than $1300. <strong>True</strong></li>
    </ul>
    <li>Blu-ray</li>
    <ul>
        <li>Blu-ray players for $130. <strong>True</strong></li>
        <li>Blu-ray movies for less than $10. <strong>False</strong></li>
    </ul>
    <li>Other</li>
    <ul>
        <li>1 TB External Hard Drive for $100. <strong>Close, $129 at Office Depot</strong></li>
        <li>Brand name HTiB for under $100.  <strong>False, Samsung HT-Z310 5.1 Dolby and DTS Home Theater System - $149.99 @ Circuit City</strong></li>
    </ul>
</ul>
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>November 20, 2008 11:47 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1557
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
					AND entry_id <> 1557
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
					FROM phpbb_topics t, phpbb_users u, phpbb_posts p, aux_phpbb_forums af
					WHERE
						t.forum_id = af.forum_id
						AND af.exclude_general = 0
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_330_-_black_friday_roundup.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
