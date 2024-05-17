<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1392";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1392 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, blu ray, next generation, theater heroes, dolby volume, theater, home, Home, Theater, new, dolby, Dolby, technology, HDTV, market, next, hdtv, blu, ray, generation, doing, Blu, see, mitsubishi, volume" />
	<meta name="description" content="With Electronic House Magazine announcing their 2008 Electronic House of the Year awards, and Kobe Bryant winning the NBA MVP award, we thought it fitting to announce some sort of recognition ourselves.  For the first time ever, we've decided to declare some 'Home Theater Heroes.'  They are companies or individuals that we believe are doing the most to drive innovation in HDTV and Home Theater.  We're either thankful for what they've done, are enjoying what they're doing, or are excited to see what they have in store.  We know everyone on this list is extremely honored just to be mentioned, after all how many times do you get a couple of guys on a Podcast announcing your company as a Home Theater Hero?  Without further ado..." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #275 - Home Theater Heroes</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_275_-_home_theater_heroes';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #275 - Home Theater Heroes'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_275_-_home_theater_heroes.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #275 - Home Theater Heroes</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May 12, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_275_-_home_theater_heroes.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_275_-_home_theater_heroes.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_275_-_home_theater_heroes.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_275_-_home_theater_heroes.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23275%20-%20Home%20Theater%20Heroes&amp;bodytext=With%20Electronic%20House%20Magazine%20announcing%20their%202008%20Electronic%20House%20of%20the%20Year%20awards%2C%20and%20Kobe%20Bryant%20winning%20the%20NBA%20MVP%20award%2C%20we%20thought%20it%20fitting%20to%20announce%20some%20sort%20of%20recognition%20ourselves.%20%20For%20the%20first%20time%20ever%2C%20we%27ve%20decided%20to%20declare%20some%20%27Home%20Theater%20Heroes.%27%20%20They%20are%20companies%20or%20individuals%20that%20we%20believe%20are%20doing%20the%20most%20to%20drive%20innovation%20in%20HDTV%20and%20Home%20Theater.%20%20We%27re%20either%20thankful%20for%20what%20they%27ve%20done%2C%20are%20enjoying%20what%20they%27re%20doing%2C%20or%20are%20excited%20to%20see%20what%20they%20have%20in%20store.%20%20We%20know%20everyone%20on%20this%20list%20is%20extremely%20honored%20just%20to%20be%20mentioned%2C%20after%20all%20how%20many%20times%20do%20you%20get%20a%20couple%20of%20guys%20on%20a%20Podcast%20announcing%20your%20company%20as%20a%20Home%20Theater%20Hero%3F%20%20Without%20further%20ado...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-13.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
With Electronic House Magazine announcing their 2008 Electronic House of the Year awards, and Kobe Bryant winning the NBA MVP award, we thought it fitting to announce some sort of recognition ourselves.  For the first time ever, we've decided to declare some 'Home Theater Heroes.'  They are companies or individuals that we believe are doing the most to drive innovation in HDTV and Home Theater.  We're either thankful for what they've done, are enjoying what they're doing, or are excited to see what they have in store.  We know everyone on this list is extremely honored just to be mentioned, after all how many times do you get a couple of guys on a Podcast announcing your company as a Home Theater Hero?  Without further ado...</p>

<p><strong>Home Theater Heroes</strong></p>

<p><strong>Television Technology<br />
<em>Pioneer</em></strong><br />
Pioneer sealed this up with their Kuro demonstration at CES in January.  We have honestly never seen a more amazing TV demo than the next generation UltraContrast Pioneer Kuro plasma.  It shows that true black, not just true 'close-to-black' is actually possible in a large screen TV without having to invent an entirely new technology.  Regardless of who ends up bringing the technology to market, it will be a huge hit, and will push all the other manufacturers and technologies to do some serious catch up work.  This is one that we're very excited to see, hopefully very, very soon.</p>

<p><strong><em>Mitsubishi</em></strong><br />
With the new LaserVue televisions hitting the streets soon, and the promise of Dual View and 3D content as well, Mitsubishi is doing an excellent job keeping the Rear Projection format relevant.  The Rear Pro market has been all but abandoned by most of the players, but Mitsubishi continues to push the technology to new limits.  For those of us who want a really big HDTV, but simply cannot afford a 60" or 70" plasma or LCD, Mitsubishi is our saving grace.  The LaserVue televisions promise color and clarity that rival any other display format, at very attractive prices.  We're really enjoying what Mitsubishi is doing for RearPro right now.</p>

<p><strong>HDTV<br />
<em>DirecTV</em></strong><br />
Hands down, DirecTV must be listed as a Home Theater Hero.  It all started with the 100 HDTV channel challenge last year.  While they didn't quite make it to 100, they came very close, and in doing so pushed all of their competitors in the same direction.  There's no telling how many HDTV channels Dish, Comcast or anyone else would have right now if it wasn't for DirecTV constantly pushing to add more channels.  This HDTV arms race has been a great thing for the consumer, and for the entire market.  The argument that "there's just nothing to watch" no longer applies, so people have less reason to push off buying a new TV.  And with a new TV comes a new DVD player, a new Surround Sound System, you name it.  Way to go DirecTV.</p>

<p><strong>Next Generation Discs<br />
<em>Sony</em></strong><br />
No, not for sticking to their guns and driving HD-DVD into oblivion, but for sticking to their guns and building a Blu-ray player into the PS3.  Back when the PS3 first shipped, or slightly before, when it was delayed, some questioned the idea of bundling in a Blu-ray player.  After all, the console got a late start in relation to the Xbox 360, and the Blu-ray market didn't even exist yet.  But it paid off.  Millions of consumers bought PS3s, which made millions of consumers into Blu-ray watchers, greatly accelerating  next generation disc adoption.  Even now Sony continues to update the PS3 to make it one of the most capable Blu-ray players on the market.</p>

<p><strong><em>Warner Brothers</em></strong><br />
Warner Brothers Studios has been on the cutting edge of next generation disc technology for a while.  At CES 2007 they announced the Total HD format, a disc that combined both Blu-ray and HD-DVD.  It sounded like a great idea for consumers, but never really got the support it needed to take off.  When that floundered, they decided to embrace the interactive technology available on HD-DVD and tried to push the boundaries of what next generation discs could do.  Then, seeing the writing on the wall, Warner was instrumental in solidifying one next generation format.  While it might have stung for a while, in the long run it will be better for everyone with only one kind of disc and player to worry about.</p>

<p><strong>Audio<br />
<em>Dolby</em></strong><br />
Some companies react, some create.  Dolby creates.  Consistently the best Demos at CES, Dolby is doing great things for the Home Theater enthusiast.  It starts with Dolby Digital plus, an amazing format that simply rocks, then moves to Dolby TrueHD.  We haven't even begun to see the full potential of what's available with this, but it will be amazing.  And next up, Dolby Volume.  We should start to see receivers with Dolby Volume built-in within a year or so, hopefully even before Christmas this year.  This technology will be the answer to all our home theater volume woes.  You won't have to turn it up to hear the dialog, just to have to turn it down when the action scenes hit; nor will you have to constantly go back and forth because the volume level of the show you're watching is so much lower than that of the commercials.  Dolby Volume is something we can't wait to see.</p>

<p><strong>Gadgets<br />
<em>Logitech</em></strong><br />
Since acquiring Harmony Remotes and Slim Devices, makers of the Squeezebox wireless audio system, Logitech has become a force in the Home Theater gadget market.  We take our share of heat for being such open Harmony remote fans, but we get ten times as many thank yous from people who've bought a Harmony and been able to quickly and easily trade ten or eleven remotes for one, very easy-to-use universal remote.  The Harmony One is quite possibly the best remote on the market, and you can get it for a fraction of the price of some of its competitors.  And the Squeezebox music system is very cool.  With the new Duet player and remote combo, the Squeezbox system is very close to being an easy, affordable, out-of-the-box, whole house audio solution.  We're working on a review of it and hope to have that on the show shortly.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May 12, 2008 11:35 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1392
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
					AND entry_id <> 1392
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_275_-_home_theater_heroes.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
