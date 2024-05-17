<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1377";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1377 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, never worry, presto service, worry getting, lost again, get, day, gift, mother, Mother, buy, Presto, Day, presto, HDTV, love, Buy, hdtv, great, remote, may, course, something, even, home" />
	<meta name="description" content="Mother's Day is coming this Sunday, May 11.  Sure, you can be like every other guy out there and get your mom or your wife, the mother of your children some flowers or a nice meal (j/k) for Mother's Day, but is that what she really wants?  Be bold, get her something she'd never expect.  She won't know how great a gift it is until a few months from now, when she realizes that it's become a part of her every day life and she can't imagine living without it.  Here are some of the best bet technology gifts for Mother's Day." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #273 - Mother's Day Gift Ideas</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #273 - Mother\'s Day Gift Ideas'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #273 - Mother's Day Gift Ideas</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>May  6, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23273%20-%20Mother%27s%20Day%20Gift%20Ideas&amp;bodytext=Mother%27s%20Day%20is%20coming%20this%20Sunday%2C%20May%2011.%20%20Sure%2C%20you%20can%20be%20like%20every%20other%20guy%20out%20there%20and%20get%20your%20mom%20or%20your%20wife%2C%20the%20mother%20of%20your%20children%20some%20flowers%20or%20a%20nice%20meal%20%28j%2Fk%29%20for%20Mother%27s%20Day%2C%20but%20is%20that%20what%20she%20really%20wants%3F%20%20Be%20bold%2C%20get%20her%20something%20she%27d%20never%20expect.%20%20She%20won%27t%20know%20how%20great%20a%20gift%20it%20is%20until%20a%20few%20months%20from%20now%2C%20when%20she%20realizes%20that%20it%27s%20become%20a%20part%20of%20her%20every%20day%20life%20and%20she%20can%27t%20imagine%20living%20without%20it.%20%20Here%20are%20some%20of%20the%20best%20bet%20technology%20gifts%20for%20Mother%27s%20Day.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-05-06.mp3">Listen Now - mp3</a>
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a>
<a href="http://www.htguys.com">Website</a>

<p><strong>Today's Show:</strong><br />
<strong>Mother's Day Gift Ideas</strong></p>

<p>Mother's Day is coming this Sunday, May 11.  Sure, you can be like every other guy out there and get your mom or your wife, the mother of your children some <a target="_blank" href="http://www.htguys.com/shop.php?id=B000EH4KXW">flowers</a> or a nice <a target="_blank" href="http://www.htguys.com/shop.php?id=B000IZU390">meal</a> (j/k) for Mother's Day, but is that what she really wants?  Be bold, get her something she'd never expect.  She won't know how great a gift it is until a few months from now, when she realizes that it's become a part of her every day life and she can't imagine living without it.  Here are some of the best bet technology gifts for Mother's Day.</p>

<p><strong>Toshiba 19AV500U 19-inch 720p LCD HDTV</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B001415FAE">Buy now</a>, $335)<br />
What's a mom going to do with a beautiful, 19" 720p HDTV with built in ATSC and QAM tuners you ask?  Love it, of course!  Nothing says "I cherish everything you do around the house" like Rachel Ray in the Kitchen (on TV, not actually doing the cooking, that costs quite a bit more).  Or how about Oprah while soaking in a nice warm bath?  And of course, you don't want to get just any old television, what kind of message would that send?  Nope, you need to get the best - something with widescreen aspect ratio, DynaLight Dynamic Backlight Control, 1440 x 900 resolution and a beautiful piano black finish.  She'll get addicted to it pretty quickly.  Oh, and maybe it will free up your other HDTV a little more often ... say if there happens to be a sporting event on or something ...  Note that this gift requires installation.</p>

<p><strong>Logitech Harmony 880 Advanced Universal Remote Control</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B00093IIRA">Buy now</a>, $149)<br />
No gift list would truly be complete without a Harmony universal remote.  If you haven't pulled the trigger yet, you owe it to her.  It's not fair that she can't even turn on the TV to watch Regis and Kelly without having to call you at work to find out why there's no sound ... Not that that's ever happened to anyone, we're just saying that it could ... Not only is the 880 a great remote, it's a great price as well.  She'll love how easy it makes your home theater, and love that you didn't spend a fortune on it.  Instead you chose to save some money so you could buy those flowers she was hoping for.  Note that this gift will require some programming.  And it may require that you let her use the remote every so often.</p>

<p><strong>Garmin StreetPilot c530 3.5-Inch Portable GPS Navigator</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B000FCSXBQ">Buy now</a>, $149)<br />
Let's face it, your wife gets tired of telling you to pull over and ask for directions.  That's where the StreetPilot c530 from Garmin comes in.  Not only will she never have to worry about getting lost again, she'll also never have to worry about you getting lost again.  This gift kills two birds with one stone - sounds like a no-brainer.  It includes an easy-to-read, automotive-grade, sunlight-readable, anti-glare display so it's perfect in the car.  Other optional software available on plug-in SD cards lets you instantly add new features to your c530 without connecting to your computer. These optional packages include the Garmin Travel Guide that gives you helpful and thorough reviews and recommendations for restaurants, hotels, shopping, nightlife, sporting events and tourist attractions.</p>

<p><strong>Logitech Wireless DJ Music System</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B000HNJ96Q">Buy now</a>, $89)<br />
Everybody love's music, but convenient music is even better.  That's what made the iPod so successful and that's why the Wireless DJ system will be a hit this Mother's Day.  You'll be a hero when she finds out that she has instant access to any CD you own in whatever room she wants.  You may need to get cracking and rip all those CDs to the computer if you haven't already, but she'll fall in love with you all over again while she's rediscovering the old CDs she hasn't listened to in years because it's just too much of a pain to get them out and switch CDs and all that.  If you've already ripped everything for an iPod, the StreamPoint Software works with all the popular media players such as iTunes, Windows Media Player, and Musicmatch for easy access to all your music.  It's a great, inexpensive wireless solution with plug-and-play simplicity, digital audio clarity, and best of all, no home network required.</p>

<p><strong>HP A10 Printing Mailbox for Presto Service</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B000JERC8A">Buy now</a>, $149)<br />
Okay, so maybe this isn't home theater related, but you still have to plug it in, so it's a techno-gadget that will work as a great gift.  Let's face it, some mom's just aren't technically inclined, so instead of fighting an uphill battle, you just embrace it with the HP A10 printing mailbox.  It works as part of the <a target="_blank" href="http://www.presto.com/">Presto</a> service, printing photos and mail sent via Presto Mail.  Presto allows anyone to send that special mom in your life email and pictures without her even needing a computer or an Internet connection.  They just send the email and it prints right out on her new HP A10.  Presto!  Presto only delivers messages from people on your Presto Friends list, so there are no ads, no spam, no junk mail. </p>

<p><strong>Panasonic Viera TH-50PZ80U 50-inch 1080p Plasma HDTV</strong> (<a target="_blank" href="http://www.htguys.com/shop.php?id=B00142OHAC">Buy now</a>, $1815)<br />
Why not pull out all the stops and show her how much you really love her with a 50" 1080p plasma HDTV from Panasonic?  It will ensure that you get more quality time with her.  Of course you may have to give in to some Grey's Anatomy or Ugly Betty, but isn't it worth it?  Of course we're referring to the more time you get to spend with her, not how great the NBA Finals, Monday Night Football or CSI will look on the new TV.  That thought never even crossed our minds.  It has a 1,000,000:1 Dynamic Contrast Ratio for the brightest whites and darkest blacks, because you know she's into that kind of thing.  Not only that, but it has 4096 shades of gradation for spectacular color reproduction, something we're sure she's been all over you about.  There's a <a target="_blank" href="http://www.htguys.com/shop.php?id=B00142MUDS">42" model</a> for about $500 less, if the 50" might be pushing it a bit.</p>

<p>No matter what you do, remember that if you screw up Mother's Day, you'll be hearing about it for a long, long time.  Think long and hard, but be bold.  No risk, no reward, right?<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>May  6, 2008 07:28 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1377
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
					AND entry_id <> 1377
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/05/hdtv_and_home_theater_podcast_273_-_mothers_day_gift_ideas.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
