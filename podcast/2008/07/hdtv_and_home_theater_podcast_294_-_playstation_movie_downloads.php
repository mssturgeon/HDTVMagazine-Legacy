<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1470";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1470 AND placement_is_primary = 1";
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
	<meta name="keywords" content="playstation store, vudu appletv, store still, high definition, advantage vudu, Vudu, vudu, Playstation, playstation, store, movies, content, AppleTV, available, appletv, features, still, shows, titles, feature, Sony, able, purchase, advantage, new" />
	<meta name="description" content="Sony announced on Tuesday that that they would begin offering movies and TV shows for download to the Playstation 3 through the Playstation store.  Movies are available from Disney, Fox and Warner Bros in addition to the obvious inclusion of Sony Pictures titles.  The system is setup much like Vudu and AppleTV, new release movies can be rented for $3.99 in standard def and $5.99 in high definition or purchased in SD for $14.99.  Older titles are available for rent for $2.99 for SD, $4.50 for HD and $9.99 for purchase.  So the big question is, &quot;How does Sony's offering stack up against Vudu or AppleTV?&quot;" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #294 - Playstation Movie Downloads</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #294 - Playstation Movie Downloads'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #294 - Playstation Movie Downloads</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>July 17, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23294%20-%20Playstation%20Movie%20Downloads&amp;bodytext=Sony%20announced%20on%20Tuesday%20that%20that%20they%20would%20begin%20offering%20movies%20and%20TV%20shows%20for%20download%20to%20the%20Playstation%203%20through%20the%20Playstation%20store.%20%20Movies%20are%20available%20from%20Disney%2C%20Fox%20and%20Warner%20Bros%20in%20addition%20to%20the%20obvious%20inclusion%20of%20Sony%20Pictures%20titles.%20%20The%20system%20is%20setup%20much%20like%20Vudu%20and%20AppleTV%2C%20new%20release%20movies%20can%20be%20rented%20for%20%243.99%20in%20standard%20def%20and%20%245.99%20in%20high%20definition%20or%20purchased%20in%20SD%20for%20%2414.99.%20%20Older%20titles%20are%20available%20for%20rent%20for%20%242.99%20for%20SD%2C%20%244.50%20for%20HD%20and%20%249.99%20for%20purchase.%20%20So%20the%20big%20question%20is%2C%20%22How%20does%20Sony%27s%20offering%20stack%20up%20against%20Vudu%20or%20AppleTV%3F%22&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-07-18.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Sony
announced on Tuesday that that they would begin offering movies and TV
shows for download to the Playstation 3 through the Playstation store. 
Movies are available from Disney, Fox and Warner Bros in addition to
the obvious inclusion of Sony Pictures titles.  The system is setup
much like Vudu and AppleTV, new release movies can be rented for $3.99
in standard def and $5.99 in high definition or purchased in SD for
$14.99.  Older titles are available for rent for $2.99 for SD, $4.50
for HD and $9.99 for purchase.  So the big question is, "How does
Sony's offering stack up against Vudu or AppleTV?"
<div><br>
<strong>Playstation Movie Downloads</strong><br>

Being
an avid Vudu fan, Braden was eager to jump into the Playstation store
and browse around.  Just getting into the store required a firmware
update to the PS3, but that was relatively quick and painless and we
were into the store.  You now have a toggle at the store to switch
between game content and video content.  Keeping the two separate is
nice.  It helps navigation a bit.  Since Vudu and AppleTV don't do
games, we'll switch the Playstation store over to video mode and start
our comparison.  Braden has both the PS3 and a Vudu, so we'll ignore
the AppleTV for today and let those two duel it out.<br id="sp910">
<br><strong>Pricing.</strong> 
As we already mentioned, pricing is nearly identical.  You have the
option to rent or purchase movies, and have the option to choose
between SD and HD.  So far we're it's a tie.<br>
<br><strong>Content.</strong>
The Playstation store is still pretty sparse.  There's no telling how
quickly they'll be able to add new movies, but when we checked it only
53 movies were available for HD download.  While Vudu has more movies,
it also has more TV content, and that TV content is available in HD. 
The Playstation store doesn't offer any TV content in HD, even when the
shows are new and were shot and broadcast in HD.  Clear advantage to
Vudu.<br>
<br><strong>Quality. </strong> We
downloaded 10,000 B.C. from the Playstation store in high definition. 
It looked good, along the same lines as what we've seen before.  Not at
all bad, but certainly not HD.  It falls into the DVD+ bucket with the
rest of these services.  Audio was only Dolby ProLogic II, not Dolby
Digital.  Overall the viewing experience was very comparable between
the PS3 and Vudu.  Tie.<br>

<br><strong>Basic Features. </strong>
Of all the movies we looked at in the Playstation store, we were only
able to find 2 that actual had previews available.  All movies, as far
as we could tell, on Vudu have previews.  This is huge.  Often times
when your just browsing around looking for something to watch, you have
no idea what half the movies are so you have to rely on previews to
find out if they're worth renting.  Plus to Vudu.<br>
<br><strong>Speed.</strong> 
Vudu is available to watch as soon as you confirm your purchase.  The
PS3 took about a minute before the movie started.  Slight edge to Vudu,
but so minor it could almost be considered a tie.<br id="tipf">

<br><strong>Interface.</strong> 
Both interfaces are easy to navigate and easy to find what you're
looking for.  Here the Playstation store really benefited from not
having too much content.  Vudu has some very sophisticated search
features that allow you to filter by Genre, MPAA rating, year of
release, etc. and also offers some great "similar movies" and "also in"
features that are really slick.  When the Playstation store starts to
get a larger library of titles, these features will be a must have. 
But for now, when you only have two dozen titles at most for each
letter of the alphabet, just categorizing by letter works fine. 
Advantage Vudu.<br>
<strong><br>
Other features.</strong> 

The Playstation store offers the unique benefit of allowing purchased
content to be transferred to the PSP so you can take the movies or TV
shows with you.  This sound like an easy feature add for the AppleTV
and the iPod, but Vudu doesn't have any way to match it.  As to our
dream of being able to "download it once and play it anywhere," this
feature sure gets a lot closer.  Big advantage to Playstation store. <br>
<br>Conclusion. 
Overall the Playstation store is still in its infancy, and that shows. 
There's still a long way to go.  It could grow up into a killer
application that puts Vudu and AppleTV to shame, but it sure isn't
there now.   Other than the cool PSP portability feature, Vudu is still
the way to go.</div>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>July 17, 2008 11:37 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1470
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
					AND entry_id <> 1470
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/07/hdtv_and_home_theater_podcast_294_-_playstation_movie_downloads.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
