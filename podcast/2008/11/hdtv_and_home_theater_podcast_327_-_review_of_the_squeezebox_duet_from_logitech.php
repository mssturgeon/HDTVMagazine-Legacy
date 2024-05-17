<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1548";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1548 AND placement_is_primary = 1";
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
	<meta name="keywords" content="original squeezebox, squeezebox duet, powered speakers, scroll wheel, incredibly easy, Duet, duet, squeezebox, Squeezebox, music, receiver, Receiver, logitech, wireless, Logitech, network, system, setup, audio, original, controller, easy, digital, squeezecenter, SqueezeCenter" />
	<meta name="description" content="There are quite a few solutions on the market for wireless digital audio. When Logitech acquired Slim Devices a couple years ago, they jumped into the fray with the Squeezebox line of products. Logitech quickly turned out their own twist on the product line, the Squeezebox Duet. We've had one in the HT Guys lab for quite a while and recently got the chance to plug it in, power it on and give it a listen." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #327 - Review of the Squeezebox Duet from Logitech</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #327 - Review of the Squeezebox Duet from Logitech'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #327 - Review of the Squeezebox Duet from Logitech</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>November 10, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23327%20-%20Review%20of%20the%20Squeezebox%20Duet%20from%20Logitech&amp;bodytext=There%20are%20quite%20a%20few%20solutions%20on%20the%20market%20for%20wireless%20digital%20audio.%20When%20Logitech%20acquired%20Slim%20Devices%20a%20couple%20years%20ago%2C%20they%20jumped%20into%20the%20fray%20with%20the%20Squeezebox%20line%20of%20products.%20Logitech%20quickly%20turned%20out%20their%20own%20twist%20on%20the%20product%20line%2C%20the%20Squeezebox%20Duet.%20We%27ve%20had%20one%20in%20the%20HT%20Guys%20lab%20for%20quite%20a%20while%20and%20recently%20got%20the%20chance%20to%20plug%20it%20in%2C%20power%20it%20on%20and%20give%20it%20a%20listen.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-11-11.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
There are quite a few solutions on the market for wireless digital audio.  When Logitech acquired Slim Devices a couple years ago, 
they jumped into the fray with the Squeezebox line of products.  Logitech quickly turned out their own twist on the product line, the 
Squeezebox Duet.  We've had one in the HT Guys lab for quite a while and recently got the chance to plug it in, power it on and give 
it a listen.<br><br>
<strong>Logitech Squeezebox Duet</strong><br>
<a target="_blank" href="http://www.htguys.com/shop.php?id=B0013IWYHU">Buy now, $315</a><br><br>
According to the Logitech Website, the Squeezebox Duet lets you: <em>"Play songs stored on your computer, tune in to thousands of Internet 
radio stations, or connect to online services such as Pandora® and Rhapsody. Plus, the multi-room controller with 2.4-inch color display 
and scroll-wheel navigation makes it easy to browse, select, and play music from the palm of your hand."</em><br><br>
We reviewed the original Squeezebox wireless music player on <a target="_blank" href="http://www.htguys.com/archive/2006/February032006.html">Episode #67</a> before it was in the Logitech fold.  Overall we were very 
satisfied.  We found some slight sync issues when multiple players were used to create a whole house audio system, but with product 
fixes and enhancements it seems that all of those issues have been solved.  That original <a target="_blank" href="http://www.htguys.com/shop.php?id=B000VZL9C2">Squeezebox</a> player is still available for 
around $200.  The original player has a built in VFD screen to help you access music and display what is currently playing.  It also 
comes with a pretty standard hard button IR remote for control.<br><br>
The Duet is quite different.  The player, or Receiver, is a small, nondescript box with one button on the front.  On the back is an 
optional Ethernet port for connecting your wired home network, the easier solution is to just connect it to your wireless (802.11g) 
network.  It has both analog stereo and digital (coax and optical) audio outputs for connecting to your amplifier, receiver or powered 
speakers.  Being so small, the Receiver is incredibly easy to place anywhere.  You can pick one up by itself for <a target="_blank" href="http://www.htguys.com/shop.php?id=B00141B1SE">$150</a>.<br><br>
But as slim and sexy as the Receiver is, that's not why you'd want the Duet.  The real fun part is the Controller.  It's a small 
iPod-like device that allows you to see your whole digital audio collection, including album art and metadata, and select it for 
playback on the Receiver.  It's very easy to use with a standard scroll wheel that responds very well to the touch.  It is responsible 
for setting up your wired or wireless connection and configuring the Receiver.  You can pick one up by itself for <a target="_blank" href="http://www.htguys.com/shop.php?id=B001413GT6">$292</a>.<br><br>
<strong>Setup</strong><br><br>
Setup involves 3 steps.  First, you install the server software, called SqueezeCenter, on a computer on your network.  SqueezeCenter 
can be run on Windows, Mac, Debian/Ubuntu, Red Hat, and Netgear ReadyNAS.  The software is very easy to install and configure.  Part 
of that setup is to point it at your digital music collection so that it can share the files with your Squeezebox players.  SqueezeCenter 
can be accessed with any Internet browser, so you can control it with any computer or portable device on the network.<br><br>
Second, you configure the Controller to get onto your wireless network.  This may involve selecting a specific network and inputting a 
security key.  Once done, you move to the third step.  That involves pressing the button on the front of the Receiver so that it can 
connect to the Controller.  The Controller then configures the Receiver to find the SqueezeCenter Server and access your entire music 
collection.  The whole setup process takes under an hour to complete.<br><br>
<strong>Use</strong><br><br>
There isn't much to say about use except that it's incredibly easy and fun.  The two nontechnical users we asked to try the system were 
Braden's wife and his eldest son, a 7 year old second grader.  Both were able to access the music library with ease and change music 
selection.  His son even found the listing of the top 50 podcasts at PodcastAlley and started playing back a recent episode of the HT 
Guys show.  Both gave it very high marks for simplicity, power and usability.<br><br>
<strong>Conclusion</strong><br><br>
Bottom line, the Duet is a great option for wireless digital music.  As game changing as the Squeezebox was when it first came out, 
the Duet builds on that legacy to create a truly polished wireless audio system.  It is every bit as powerful as the Sonos, but costs 
only a fraction of the price.  The Duet runs for around $315 while an equivalent Sonos setup will cost you approximately $750.  And you 
can add zones to the Duet system at will for only $150 per zone.  If you happen to have any of the original Squeezebox players, they're 
completely compatible with the Duet and plug right in as extra zones, playing right in sync the the Duet Receivers.<br><br>
If you'd like to have music in every room of your home, you can start with a Duet in one room and add more rooms for only $150 as your 
budget allows.  In those rooms where a small executive system might work out well, Logitech also offer the Squeezebox Boom.  It's very 
similar to the original Squeezebox, but includes built in speakers.  No need to connect it to a stereo, home theater or pair of powered 
speakers.  It goes for only <a target="_blank" href="http://www.htguys.com/shop.php?id=B001DJ64D4">$279</a>.  We haven't had the chance to use one yet, but we'll try to get one so we can comment on it.
<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>November 10, 2008 11:03 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1548
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
					AND entry_id <> 1548
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/11/hdtv_and_home_theater_podcast_327_-_review_of_the_squeezebox_duet_from_logitech.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
