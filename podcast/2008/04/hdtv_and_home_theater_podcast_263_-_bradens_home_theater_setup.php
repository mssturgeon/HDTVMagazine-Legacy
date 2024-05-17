<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1323";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1323 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, longer available, dvd player, blu ray, ray player, theater, home, braden, Braden, great, player, DVD, dvd, longer, available, back, Home, Theater, blu, ray, Denon, still, remote, get, Blu" />
	<meta name="description" content="So by now everyone knows what's in Ara's Home Theater, in fact all over Ara's house, but we get questions occasionally on what Braden is using as well.  To put those questions to rest, we'll get into all the details today.  You've heard bits and pieces, like the recent addition of the PS3, but here's the whole system, all at one time." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #263 - Braden's Home Theater Setup</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #263 - Braden\'s Home Theater Setup'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #263 - Braden's Home Theater Setup</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>April  1, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23263%20-%20Braden%27s%20Home%20Theater%20Setup&amp;bodytext=So%20by%20now%20everyone%20knows%20what%27s%20in%20Ara%27s%20Home%20Theater%2C%20in%20fact%20all%20over%20Ara%27s%20house%2C%20but%20we%20get%20questions%20occasionally%20on%20what%20Braden%20is%20using%20as%20well.%20%20To%20put%20those%20questions%20to%20rest%2C%20we%27ll%20get%20into%20all%20the%20details%20today.%20%20You%27ve%20heard%20bits%20and%20pieces%2C%20like%20the%20recent%20addition%20of%20the%20PS3%2C%20but%20here%27s%20the%20whole%20system%2C%20all%20at%20one%20time.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-04-01.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
So by now everyone knows what's in Ara's Home Theater, in fact all over Ara's house, but we get questions occasionally on what Braden is using as well.  To put those questions to rest, we'll get into all the details today.  You've heard bits and pieces, like the recent addition of the PS3, but here's the whole system, all at one time.</p>

<p><strong>Braden's Home Theater Setup</strong></p>

<p><strong>Television: <a title="JVC" target="_blank" href="http://www.jvc.com/">JVC</a> HD-61FH97 HD-ILA 61" HDTV</strong> (No longer available)<br />
Back in 2006 we went on 'Projector Quest' to find the right projector for Braden's house. The room could support a 100" screen, so why not, right?  As time went on he realized that there was just too much ambient light to make it a viable option, so he dropped back to rear projection.  Sure 61" is big, but now that everyone (meaning the finance committee), has had a chance to adjust to the larger screen, it looks like it could go a bit bigger. Next home theater upgrade: 70" or bigger.</p>

<p><strong>Receiver: <a title="Denon" target="_blank" href="http://usa.denon.com/">Denon</a> AVR 3806 7.1 channel AV receiver</strong> (No longer available)<br />
Receivers are like ice cream, they're all pretty good, but everyone has their favorite flavor. After using some other brands that shall remain nameless, Braden bought his first Denon receiver in 2001 and hasn't looked back. It's been nothing but Denon in the Russell household, including several others in other rooms of the house. The AVR 3806 has it's rough edges, especially in setup and configuration, but it sounds great and just flat out works. Not to say that there's anything wrong with other brands, Denon just happens to be Braden's favorite flavor. If you haven't tried it, you should at least give it a chance.</p>

<p><strong>Speakers: <a title="Klipsch" target="_blank" href="http://www.klipsch.com/">Klipsch</a> reference series</strong> (No longer available)<br />
6 x <a title="R-5800-C" target="_blank" href="http://www.klipsch.com/products/discontinued/details/r-5800-c.aspx">R-5800-C</a> In-ceiling Loudspeakers<br />
1 x <a title="RC-7" target="_blank" href="http://www.klipsch.com/products/details/rc-7.aspx">RC-7</a> Center channel speaker<br />
1 x <a title="RSW-12" target="_blank" href="http://www.klipsch.com/products/discontinued/details/rsw-12.aspx">RSW-12</a> Subwoofer<br />
There's not much more you can say about Klipsch that hasn't been said. They're one of the biggest players in movie theater sound and they make great, bang for the buck, home theater products as well. They've been around for a long time and still do all their own design work. Many speaker companies these days just buy outsourced horns and put them into pretty packages. Klipsch is know for making very efficient speakers, so they sound good not matter what you connect them to. They'll work well with a bargain receiver, but sound amazing with a great amp. The in-ceiling choice was mostly a requirement of the aesthetics committee, but also<br />
worked better from a practical standpoint. The room doesn't have a wall on the back or right side, so speaker placement is challenging. The horns in the speakers can be aimed, giving exactly the same effect you'd get if you mounted actual speakers to the ceiling and pointed them down. The only drawback is the noise that feeds into the rooms upstairs.</p>

<p><strong>Blu-ray player: <a title="Sony PlayStation 3" target="_blank" href="http://www.sonystyle.com/webapp/wcs/stores/servlet/CategoryDisplay?storeId=10151&amp;catalogId=10551&amp;langId=-1&amp;categoryId=6148914691233368166">Sony PlayStation 3</a>, 80 GB model</strong> (No longer available, 40 GB model: $399 MSRP)<br />
Yes, the PS3 is in the home theater primarily as a Blu-ray player, but it also happens to be a great gaming platform. Who knew? As a Blu-ray player it's top notch, and now supports BD-Live for interactive Blu-ray content. The expandability and upgrade-ability are great. You can also use the PS3 to play movies, listen to music and look at picture over your home network. The interface isn't ideal, but it works in a pinch. Overall a great idea for those looking to buy into HD movies now that there's a clear winner.</p>

<p><strong>DVD Player: Toshiba HD-A2 HD-DVD Player</strong> (No longer available)<br />
Sure the PS3 could work as a standard DVD player, but the A2 does a great job at upconversion, still allows for the occasional viewing of an HD-DVD movie (like, let's say, Transformers or something like that), and integrates really well into the Home Theater using an IR-based universal remote. If you have an OPPO for DVDs, you don't need an HD-DVD player, but if you don't have an upconverting player, by all means pick up an HD-A3 for $80 bucks. It's still a no-brainer, even if you never watch an HD-DVD on it in your life.</p>

<p><strong>Remote: <a title="Logitech Harmony" target="_blank" href="http://www.logitech.com/index.cfm/harmony_truth_2008/4238&amp;cl=us,en?WT.mc_id=usym_/getharmony_harmony-truth-2008_amr-us&amp;strf=Universal_Symlink&amp;ci_tag=ht2008_logi-usym-getharmony&amp;gclid=CJu4zdW5q5ICFRkGagodpSPFQQ">Logitech Harmony</a> One Advanced Universal Remote</strong> (MSRP: $249, <a title="Buy now" target="_blank" href="http://http/;//www.htguys.com/shop.php?id=B00119T6NQ">Buy now</a>)<br />
This one is pretty self explanatory. Harmony remote. Touch screen. Cool and sexy. Enough said. For more detail check out <a title="Episode #258" target="_blank" href="/archive/2008/March14.html" id="p:7y">Episode #258</a>.</p>

<p><strong>HDTV Sources: Dish ViP622 and Motorola DCH3416 (Cox Cable)</strong><br />
Between Dish, Cox Cable and Over the Air there's always something to watch in high definition. Sure, the bills may be excessive, but it's all about the podcast. What happens if we need to compare the quality of the Super Bowl between Cable and Satellite, what ever would we do? Ok, so maybe there's a slight addiction here, but we come to accept it, and so has the finance committee.</p>

<p><strong>Other bits and pieces:</strong><br />
The system requires an HDMI switch to make everything work. Braden has the 5x1 switch from <a title="Monoprice" target="_blank" href="http://www.monoprice.com/" id="vhia">Monoprice</a> and it's perfect. Even running every component as HDMI only, there are<br />
still way too many cables back behind the components. You have to remember that they all need power, there's a ton of speaker wires, and just about everything these days needs a network connection. That brings us to the next piece, a 5 port Ethernet switch. These are a dime a dozen, any brand works. Just make sure you get a switch and not a hub. And for all those power cables Braden uses a <a title="Monster reference" target="_blank" href="http://www.monstercable.com/power/home_theater/reference_home_theater_power.asp">Monster reference</a> power center, but mostly for the insurance policy.</p>

<p><strong>Elsewhere:</strong><br />
How can Braden be such a Panasonic Plasma nut and not have one in the home theater? Great question. There are a ton of other devices scattered around the house, including a Panasonic Plasma, a Samsung LCD, an XBox and an XBox360, a couple OPPO DVD players, Logitech Squeezebox digital music players, etc. But as for the home theater, that's what's there. Looking at how many things are 'no longer available,' it might be the right season for a few upgrades...</p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>April  1, 2008 12:11 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1323
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
					AND entry_id <> 1323
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/04/hdtv_and_home_theater_podcast_263_-_bradens_home_theater_setup.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
