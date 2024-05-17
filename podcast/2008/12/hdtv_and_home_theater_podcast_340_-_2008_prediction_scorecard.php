<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1585";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1585 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, media center, hdmi cec, wireless hdmi, living room, result, Result, blu, ray, Blu, sure, hdmi, year, HDMI, make, see, apple, Apple, still, DVD, huge, dvd, really, new, already" />
	<meta name="description" content="Technology moves faster now than it ever has before.  Things that were cutting edge just a year ago are almost antiques already.  2008 saw some really great and exciting things in the world of HDTV and Home Theater.  Back in January we tried to predict what we thought would happen.  Let's take a look at our predictions to see how we fared." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #340 - 2008 Prediction Scorecard</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #340 - 2008 Prediction Scorecard'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #340 - 2008 Prediction Scorecard</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>December 25, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23340%20-%202008%20Prediction%20Scorecard&amp;bodytext=Technology%20moves%20faster%20now%20than%20it%20ever%20has%20before.%20%20Things%20that%20were%20cutting%20edge%20just%20a%20year%20ago%20are%20almost%20antiques%20already.%20%202008%20saw%20some%20really%20great%20and%20exciting%20things%20in%20the%20world%20of%20HDTV%20and%20Home%20Theater.%20%20Back%20in%20January%20we%20tried%20to%20predict%20what%20we%20thought%20would%20happen.%20%20Let%27s%20take%20a%20look%20at%20our%20predictions%20to%20see%20how%20we%20fared.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<!--HDTV and Home Theater Podcast #-->
<div align="center" style="height:55px; padding-top:20px">
<span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span>
<span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-12-26.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
Technology moves faster now than it ever has before.  Things that were cutting edge just a year ago are almost antiques already.  
2008 saw some really great and exciting things in the world of HDTV and Home Theater.  Back in January we tried to predict what 
we thought would happen.  Let's take a look at our predictions to see how we fared.
<br><br>
<strong>2008 Prediction Scorecard</strong>
<br><br>
1. HD-DVD and Blu-ray will not unify.  There's no way to "unify" the formats without totally alienating all the existing owners 
and without both camps making some huge concessions.  Toshiba may be willing to talk, but Sony doesn't do concessions.  Ara 
believes there's too much momentum behind Blu-ray, by the end of the year, although HD-DVD will not have closed up shop, Blu-ray 
will be the de-facto winner.  Braden believes that we'll see more of the same in 2008, both sides will continue to slug it out 
and the war will drag on for one more year.
<br><br>
<em>Result: Close, but then again, so far off.  Sure the two formats didn't unify, so we get partial credit on that.  But 
obviously Blu-ray won.  Ara was the closest with his prediction; Braden flat out missed the boat.</em>
<br><br>
2. Microsoft will make a huge push into the living room.  Assuming everyone already has a Vista computer, they'll subsidize 
Media Center Extenders, to try to push them into your family room.  On top of that, they'll put together a VUDU style movie 
download offering to seal the deal.
<br><br>
<em>Result: Wrong.  Microsoft didn't make a huge push.  The XBox 360 is selling well, and Vista Media Center is gaining a 
little traction, but there wasn't a "huge push" from Microsoft.  We could characterize 2008 as "more of the same" in that 
regard.</em>
<br><br>
3. Stand-alone Blu-ray player prices fall.  Blu-ray players start to enter mass market pricing and sales will pick up 
dramatically.  This will give Sony the confidence they need to not give in to any unification talks.  As if the 3 million 
PS3's weren't enough.
<br><br>
<em>Result: Dead on.  Blu-ray prices fell to below $150 this year.  We'll just ignore that comment on unification talk.  Blu-ray 
prices fell ... although we haven't really seen mass adoption yet.  Perhaps 2009?</em>
<br><br>
4. VUDU will gain momentum and make big strides in 2008.  They will find a new pricing model that makes more sense to the 
Netflix style user who's already used to the all-you-can-eat model.  As they make more money, hardware prices will drop and 
the boxes will fly out of the warehouse.
<br><br>
<em>Result: Partial credit.  Vudu has done some really good things, they have the newer players and the HDX format, which is 
really awesome.  But they've also had their share of difficulty including a pretty good size layoff.  Still around, still a 
player, but no new pricing model and no boxes flying out of the warehouse.</em>
<br><br>
5. Portable HD-DVD players hit the market.  To combat the surging Blu-ray player sales, Toshiba starts selling other forms 
of HD-DVD players, like portable units, and in-car units.  They'll subsidize them as well, to make sure they sell.
<br><br>
<em>Result:  Move on, nothing to see here...It's hard to believe the war was still as strong an it was just one year ago.  It 
feels like ancient history now.  We'd sure like to think all of our predictions around HD-DVD were ancient history.  So sad.</em>
<br><br>
6. Apple will redo the Apple TV as a true media center device.  This essentially means the Apple TV will go away and the Mini
 be re-branded with some new software.  But Steve Jobs won't be content to just say that the Apple TV foray into the living 
 room was a failure and walk away.  They'll also increase the quality of movie downloads and make it easy to put them in the 
 living room or on your iPod.
<br><br>
<em>Result:  Wrong.  Apple didn't do much with the Apple TV.  They added some new media and purchase/rental options, added a 
bell here and a whistle there, but no media center, that's for sure.  This is probably Ara's biggest disappointment on the list.  
Looking back, we aren't sure if this was a prediction or something we wanted and hope for so bad, we put it on the list with the 
hope that we could will it into existence.</em>
<br><br>
7. Wireless HDMI will come to market.  Companies will start to sell wireless HDMI devices, in readily available quantities, by 
the ends of the year.  They will be stand-alone HDMI cable replacements, and net yet built into your typical consumer electronics 
device, that will come in 2009.
<br><br>
<em>Result:  Correct-ish.  Wireless HDMI did come to market.  Hundreds of thousands of chips have shipped.  But are the devices 
shipping in "readily available quantities?"  Maybe not so much.  This one was really close.</em>
<br><br>
8. LCD HDTV sales will skyrocket.  We'll see a huge up-tick in sales of small, inexpensive LCD HDTVs.  Due in part to the 
limited availability of CRT televisions, and all the hype that will be surrounding the analog cut-off in 2009, people will 
pick up a new 27-32" LCD in droves to make sure they're ready.
<br><br>
<em>Result: Nailed it.  This was the safe bet.  We were already seeing momentum in this direction early in the year.  But by 
the end of 2008 the LCD has emerged as the dominant TV format, in North America at least.</em>
<br><br>
9. Reality shows will go high def.  The argument against HD will be overshadowed by the pressure to go HD this year.  Shows 
that have never been in HD will have to do so in the fall, just to stay relevant.  Some, like Dancing with the Stars and 
American Idol, are already there; others will join.
<br><br>
<em>Result:  We aren't sure.  Unfortunately we aren't sure how to score this.  Neither of us are big reality show fans.  Sure 
some reality shows are in HD, but many others are still in SD.  We don't know if there are more on one side or the other.  We 
do know, however, that Survivor finally went HD, so we did good on that one.</em>
<br><br>
10. HDMI-CEC will take hold.  As consumers buy HDTVs, and connect them with HDMI cables, they'll start to see the power of the 
HDMI-CEC (Consumer Electronics Control) protocol.  Manufacturers will finally figure out a way to tell consumers that, even 
though they have their own special name for it (EZ-Sync, Anynet+, SimpleLink, etc.), the protocol will actually work with other 
manufacturers devices as well.
<br><br>
<em>Result: Close.  We do see a version of HDMI-CEC on almost every home theater device being sold today.  Some are talking about 
interoperability, but not all.  There's still a ways to go on this one, but it's moving in the right direction.</em>
<br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>December 25, 2008 10:57 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1585
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
					AND entry_id <> 1585
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/12/hdtv_and_home_theater_podcast_340_-_2008_prediction_scorecard.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
