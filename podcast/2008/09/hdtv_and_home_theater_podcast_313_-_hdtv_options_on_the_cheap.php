<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1478";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1478 AND placement_is_primary = 1";
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
	<meta name="keywords" content="basic cable, monthly fee, shows movies, small monthly, small investment, HDTV, hdtv, cable, hulu, Hulu, basic, antenna, small, monthly, options, get, netflix, show, even, watch, Netflix, cost, available, movies, most" />
	<meta name="description" content="There's a lot of talk about the economy these days.  Things are slowing down and people are trying more than ever to make their hard earned money work for them.  We don't claim to be financial advisers or economic experts, we don't even play them on the Internet, but we do have a few options for those looking to enjoy HDTV while riding out a difficult economy.  Because as we all know, some things in life are optional, Starbucks, Swedish massages, expensive creams and lotions ... but HDTV is not." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #313 - HDTV Options on the Cheap</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #313 - HDTV Options on the Cheap'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #313 - HDTV Options on the Cheap</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>September 22, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23313%20-%20HDTV%20Options%20on%20the%20Cheap&amp;bodytext=There%27s%20a%20lot%20of%20talk%20about%20the%20economy%20these%20days.%20%20Things%20are%20slowing%20down%20and%20people%20are%20trying%20more%20than%20ever%20to%20make%20their%20hard%20earned%20money%20work%20for%20them.%20%20We%20don%27t%20claim%20to%20be%20financial%20advisers%20or%20economic%20experts%2C%20we%20don%27t%20even%20play%20them%20on%20the%20Internet%2C%20but%20we%20do%20have%20a%20few%20options%20for%20those%20looking%20to%20enjoy%20HDTV%20while%20riding%20out%20a%20difficult%20economy.%20%20Because%20as%20we%20all%20know%2C%20some%20things%20in%20life%20are%20optional%2C%20Starbucks%2C%20Swedish%20massages%2C%20expensive%20creams%20and%20lotions%20...%20but%20HDTV%20is%20not.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-09-23.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
There's a lot of talk about the
economy these days.&nbsp; Things are slowing down and people are trying more
than ever to make their hard earned money work for them.&nbsp; We don't
claim to be financial advisers or economic experts, we don't even play
them on the Internet, but we do have a few options for those looking to
enjoy HDTV while riding out a difficult economy.&nbsp; Because as we all
know, some things in life are optional, Starbucks, Swedish massages,
expensive creams and lotions ... but HDTV is not.<br>

<br><strong>HDTV Options on the Cheap</strong><br>
<strong><br>
Over the Air TV</strong><br>
HDTV
is available for in many different places all across the globe.&nbsp;
Certainly here in the United States, if you have a big enough antenna,
odds are you can get crystal clear HDTV with Dolby Digital 5.1 sound
for not monthly fee whatsoever.&nbsp; In fact not only is it free, it tends
to be the best quality broadcast available.&nbsp; All you need is the
antenna and a tuner and you can watch all of the major networks (NCB,
CBS, ABC, FOX) without paying a penny.&nbsp; Clearly no one should be
without HDTV even in the worst of times.<br>
<br><strong>Basic Cable</strong><br>

Basic
cable tends to give you a handful of analog channels for a very
reasonable monthly cost.&nbsp; Of course most people don't have basic
cable.&nbsp; They upgrade to digital and then to HDTV, add a few movie
channels and voila, you're paying over $100 per month.&nbsp; You can save a
ton of money every month by cutting that back tremendously.&nbsp; And here's
the kicker.&nbsp; With that basic cable package you tend to get all your
local channels - in SD and in HD!&nbsp; That's right, most Cable companies
broadcast the locals in HD unencrypted.&nbsp; <br>
<br>You get all the
benefits of over-the-air for a small monthly fee, but no antenna is
required.&nbsp; If you live too far from the broadcast towers for the
antenna option to work, basic cable is a great idea.&nbsp; All you need for
this one is a QAM tuner.&nbsp; While they aren't as standard as the ATSC
tuners needed for over-the-air, most HDTVs have them built in.&nbsp; And you
get the added benefit of being able to randomly tune in what your
neighbors are watching on Pay-Per-View.&nbsp; Be careful, though, you never
know what they could be into.<br>

<br><strong>Hulu</strong><br>
<a title="Hulu" target="_blank" href="http://www.hulu.com/" id="h7a.">Hulu</a>
is a website that streams TV shows and movies for free.&nbsp; They have a
limited number of commercials, but what doesn't these days?&nbsp; Even
websites have commercials now.&nbsp; Hulu has a bunch of movies and TV shows
including some current programming like the brand new Knight Rider, The
Office, The Daily Show and Lipstick Jungle.&nbsp; Hulu is working on HD
content.&nbsp; While it isn't available for the stuff we want to watch yet,
it could be soon.&nbsp; And the stuff they have isn't that bad.&nbsp; With a
small investment in PlayOn, you can stream Hulu content to your Xbox
360 or PS3.&nbsp; Another free TV option.&nbsp; Not HD, but could be.<br>

&nbsp; <br>
<strong>Netflix</strong><br>
Where
else can you have access to any movie you'd ever want to watch for only
$8.99 a month.&nbsp; For the cost of a larger burger combo with fries, you
have over 100,000 DVD titles at your fingertips.&nbsp; There's also the
12,000 TV shows and movies available for instant streaming to your
computer, or your TV with a small investment in a player.&nbsp; If you
prefer watching movies or don't mind waiting for a show's season and
just renting the DVD, <a title="Netflix" target="_blank" href="http://www.netflix.com/" id="ys3q">Netflix</a>
is a great option.&nbsp; We're still very optimistic that Netflix will begin
to add newer and newer titles to the instant viewing library so you
don't even have to wait for the DVD to show up in the mail.<br>

<br>If
you look hard enough, there are options out there.&nbsp; Combining
over-the-air TV and Hulu gets you a solution for a small upfront
investment (antenna and potentially a Hulu player) but zero monthly
cost.&nbsp; Stepping up to basic cable and Netflix provides more content
than most people can watch in a week, reduces the upfront cost but
incurs a small monthly fee.&nbsp; There are options out there.&nbsp; You
shouldn't have to go without HDTV.&nbsp; Do your homework and you'll find
the solution that's right for you.<br><br><br>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>September 22, 2008 10:33 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1478
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
					AND entry_id <> 1478
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_313_-_hdtv_options_on_the_cheap.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
