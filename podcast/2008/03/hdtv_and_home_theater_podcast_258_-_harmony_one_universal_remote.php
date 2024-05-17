<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1296";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1296 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, universal remote, harmony universal, touch screen, remote box, Harmony, harmony, remote, theater, home, programming, buttons, screen, easy, button, software, setup, universal, new, need, great, Remote, Universal, touchscreen, sexy" />
	<meta name="description" content="This week we finally get to the review at lot of listeners have been asking for, the Harmony One Advanced Universal Remote by Logitech.  We reviewed the Harmony 880 almost three years ago, and although Ara questioned the price premium, it eventually became our standard universal remote recommendation.  Now with the release of the One, we were eager to see if it would replace the 880 in our hearts and minds.  It won the 'Best of Innovations' award in the Home Theater Accessories category at CES 2008.  You can find it in retail stores and online for an MSRP of $250 US." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #258 - Harmony One Universal Remote</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #258 - Harmony One Universal Remote'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #258 - Harmony One Universal Remote</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>March 13, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23258%20-%20Harmony%20One%20Universal%20Remote&amp;bodytext=This%20week%20we%20finally%20get%20to%20the%20review%20at%20lot%20of%20listeners%20have%20been%20asking%20for%2C%20the%20Harmony%20One%20Advanced%20Universal%20Remote%20by%20Logitech.%20%20We%20reviewed%20the%20Harmony%20880%20almost%20three%20years%20ago%2C%20and%20although%20Ara%20questioned%20the%20price%20premium%2C%20it%20eventually%20became%20our%20standard%20universal%20remote%20recommendation.%20%20Now%20with%20the%20release%20of%20the%20One%2C%20we%20were%20eager%20to%20see%20if%20it%20would%20replace%20the%20880%20in%20our%20hearts%20and%20minds.%20%20It%20won%20the%20%27Best%20of%20Innovations%27%20award%20in%20the%20Home%20Theater%20Accessories%20category%20at%20CES%202008.%20%20You%20can%20find%20it%20in%20retail%20stores%20and%20online%20for%20an%20MSRP%20of%20%24250%20US.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<p><br><br />
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-03-11.mp3">Listen Now - mp3</a><br />
<br><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<br><br />
<a href="http://www.htguys.com">Website</a><br />
<br><br />
<br><br />
<div><strong>Today's Show:</strong></div><br />
We finally got to the review at lot of listeners have been asking for, the <a href="http://www.logitech.com/index.cfm/remotes/universal_remotes/devices/3898&amp;cl=us,en" target="_blank">Harmony One</a> Advanced Universal Remote by <a href="http://www.logitech.com/" target="_blank">Logitech</a>. We reviewed the <a href="http://www.logitech.com/index.cfm/remotes/universal_remotes/devices/372&amp;cl=us,en" target="_blank">Harmony 880</a> almost three years ago, and although Ara questioned the price premium, it eventually became our standard universal remote recommendation. Now with the release of the One, we were eager to see if it would replace the 880 in our hearts and minds. It won the 'Best of Innovations' award in the Home Theater Accessories category at CES 2008. You can find it in retail stores and online for an MSRP of $250 US (<a href="http://www.htguys.com/shop.php?id=B00119T6NQ" target="_blank">Buy now</a>).</p>

<p><strong>Harmony One Universal Remote</strong></p>

<p><strong>Setup</strong><br />
The reason we really fell in love with Harmony so many years ago is the setup.  It's amazingly easy and the One is no different.  If you've ever programmed a Harmony before, you know how easy it is; setup literally takes 20 minutes from the time you pull the remote out of the box until it's in your home theater working like a charm.  Harmony has a 'Replace Remote' button in the programming software so that if you already own one, you can transfer the settings to the new remote in a matter of seconds, shaving about 19 minutes off the total setup time. </p>

<p>In addition to the remote, the box includes a charging base with power cord, a USB cable for programming, a rechargeable battery, and a CD with the programming software. </p>

<p>Harmony programming used to be done completely online.  They now have a cool desktop application that connects back to the Internet for you, making the user interface much more responsive.  Of course a live Internet connection is still required, but that's not too difficult for our audience.  After installing the software, you log into your account (or create a new one) and walk through the wizard to set it up.  First you input all the devices in your home theater, which is as easy as entering a manufacturer and a model number.  Harmony then walks you through setting up activities, like "Watch TV" or "Watch DVD" so that you can control everything seamlessly.  You have the ability to fine tune the programming, but most users won't need to do too much. </p>

<p><strong>Design</strong><br />
The layout of the remote is very familiar, an LCD screen at the top, navigation controls in the middle, and transport and keypad on the bottom.  Traditionally the biggest challenge with Harmony remotes has been button size; they're simply way too small.  The One, however, introduces larger buttons that are very easy to use.  It fits in your hand very well and has a nice balance.  The coolest new feature is the touch screen.  While the 880 has a nice color screen on it, it requires that you press a tiny button on the side of the screen to activate an activity or use the custom buttons that can be there.  With the One, you just touch the button or activity.  Touchscreen remotes are very sexy, but they tend to be difficult to use because of the lack of hard buttons and the need to "page" through a bunch of screens to find the button you need.  The Harmony One is a great blend between sexy touchscreen and functional hard buttons. </p>

<p><strong>Use</strong><br />
Like the 880, the One is rechargeable, so there's never a need to replace batteries.  This may sound like a small thing, but when your remote goes out and you can't scrounge up batteries for it in the house, you're in trouble.  Functionally it is very similar to the 880; it lights up when you move it, has the same "help" feature to guide you when devices get out of sync, and does a great job controlling the home theater by activity rather than device.</p>

<p>You can trick out the One by adding cool channel icons to the touch screen for your favorite channels.  The software only seems to include icons for Fox channels, but you can download a zip package full of other icons from <a href="http://www.iconharmony.com/watchtv/index.html" target="_blank">IconHarmony.com</a> </p>

<p><strong>Conclusion</strong><br />
As with any Harmony remote, the One is a great choice for your home theater.  It's new, sexy and easy to use.  The touchscreen is cool and the larger buttons make it a bit easier to use.  But overall it doesn't represent a huge departure from the 880.  Of course we're gadget freaks, so we'll both be using them, but if you want to save some money, the 880 remains a great option.  Bang for the buck, the 880 is still probably the way to go.  For the coolness factor, the One is where it's at.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>March 13, 2008 10:27 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1296
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
					AND entry_id <> 1296
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/03/hdtv_and_home_theater_podcast_258_-_harmony_one_universal_remote.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
