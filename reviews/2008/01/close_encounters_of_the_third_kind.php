<?
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 842";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 842 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $GOOGLE_CHANNEL['The HT Guys'];

	# This template is used only for articles and bulletins. Reviews are generated from a separate template
	switch (8) {
		case 1: # Articles
			$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$container = 'article_container';
			$category_page = 'category.php';
			break;
		case 6: # Test
			$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-articles" />';
			$container = 'article_container';
			$category_page = 'category.php';
			break;
		case 7: # Bulletins
			$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
			$container = 'bulletin_container';
			$category_page = 'bulletins-category.php';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="close encounters, special features, blu ray, third kind, encounters third, movie, audio, special, encounters, scenes, Close, Encounters, close, review, sound, features, disc, scene, blu, ray, Special, Ara, still, Blu, kind" />
	<meta name="description" content="Since we received positive feedback on the Transformers technical review we did a few months back, we decided to make them monthly features. We'll alternate between Blu Ray Discs and HD DVD discs. For our second technical movie review we chose Close Encounters of the Third Kind. I (Ara) remember watching this movie as a teenager. I was about 15 years old and remember how I marveled at the special effects and cinematography. When the 30th Anniversary version came out on Blu Ray I knew that I was going to buy it and make it part of my library. I wondered how would a movie that was made in the late 70's hold up technically after all these years. Overall, we have to say it help up pretty darn good. It may not be a movie you use to show off your new HDTV and Blu Ray player but it did provide much entertainment for the entire family." />
	<title>HDTV Magazine Reviews: Close Encounters of the Third Kind</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		$pagetag = "NTPT_PGEXTRA = 'author=". $AUTHOR_ID['The HT Guys'] ."';\n";
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/close_encounters_of_the_third_kind';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Close Encounters of the Third Kind'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2008/01/close_encounters_of_the_third_kind.php";
		if (array_key_exists('The HT Guys', $AUTHOR_PORTRAIT) && 8 <> 7) {
			$img = '<img src="'. $AUTHOR_PORTRAIT['The HT Guys'] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Close Encounters of the Third Kind</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>January  4, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=Blu-ray Movies&id=<?=$category_id?>">Blu-ray Movies</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/01/close_encounters_of_the_third_kind.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2008/01/close_encounters_of_the_third_kind.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2008/01/close_encounters_of_the_third_kind.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_ARTICLES) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new articles:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				HDTV Magazine Articles via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new articles:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				HDTV Magazine Articles via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/01/close_encounters_of_the_third_kind.php&amp;phase=2&amp;title=Close%20Encounters%20of%20the%20Third%20Kind&amp;bodytext=Since%20we%20received%20positive%20feedback%20on%20the%20Transformers%20technical%20review%20we%20did%20a%20few%20months%20back%2C%20we%20decided%20to%20make%20them%20monthly%20features.%20We%27ll%20alternate%20between%20Blu%20Ray%20Discs%20and%20HD%20DVD%20discs.%20For%20our%20second%20technical%20movie%20review%20we%20chose%20Close%20Encounters%20of%20the%20Third%20Kind.%20I%20%28Ara%29%20remember%20watching%20this%20movie%20as%20a%20teenager.%20I%20was%20about%2015%20years%20old%20and%20remember%20how%20I%20marveled%20at%20the%20special%20effects%20and%20cinematography.%20When%20the%2030th%20Anniversary%20version%20came%20out%20on%20Blu%20Ray%20I%20knew%20that%20I%20was%20going%20to%20buy%20it%20and%20make%20it%20part%20of%20my%20library.%20I%20wondered%20how%20would%20a%20movie%20that%20was%20made%20in%20the%20late%2070%27s%20hold%20up%20technically%20after%20all%20these%20years.%20Overall%2C%20we%20have%20to%20say%20it%20help%20up%20pretty%20darn%20good.%20It%20may%20not%20be%20a%20movie%20you%20use%20to%20show%20off%20your%20new%20HDTV%20and%20Blu%20Ray%20player%20but%20it%20did%20provide%20much%20entertainment%20for%20the%20entire%20family.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2008/January04.html">http://www.htguys.com/archive/2008/January04.html</a></center>
<br />

<p><a href="http://www.htguys.com/shop.php?id=B000VECACG">Close Encounters of the Third Kind</a> - Blu Ray Disc Review</p>

<p>Since we received positive feedback on the Transformers technical review we did a few months back, we decided to make them monthly features. We'll alternate between Blu Ray Discs and HD DVD discs. For our second technical movie review we chose Close Encounters of the Third Kind. I (Ara) remember watching this movie as a teenager. I was about 15 years old and remember how I marveled at the special effects and cinematography. When the 30th Anniversary version came out on Blu Ray I knew that I was going to buy it and make it part of my library. I wondered how would a movie that was made in the late 70's hold up technically after all these years. Overall, we have to say it help up pretty darn good. It may not be a movie you use to show off your new HDTV and Blu Ray player but it did provide much entertainment for the entire family.</p>

<p><strong>What's in the Box</strong><br />
This is a two disc set. Disc one contains three versions of the movie (Theatrical, Special Edition, and Director's Cut). Disc two contains the special features which we'll talk about  later in this review. There is also a book of behind the scenes photos, movie trivia and some bios. Finally, there is a small movie poster that contains a time line on the reverse side that indicates which scenes were added or removed between the three versions.</p>

<p><br />
<strong>Video</strong><br />
This area was kind of a disappointment for us. We were hoping for something special but got something that was all over the map. Some scenes were fantastic. These were mostly the outdoor stuff. A lot of the night shots looked very grainy. In fact there were some daylight shots that were grainy as well. The other thing that bothered us was the focus. Sometimes you would see the foreground in focus and the background was horribly out of focus. Now we know that's how its supposed to be but in HD it just looked bad.</p>

<p>Special effects either held their own or just fell apart. There was nothing that reminded us how in awe we were when seeing the film in the theater. Maybe our expectations are just too high now. We still enjoyed the ending even though you could tell what was filmed in front of a green screen (or 1977 equivalent). Please don't get us wrong. We (mainly Ara) still loved and enjoyed the movie. In fact, if Ara wasn't being so critical while watching, his family would have only noticed the some of the artifacts. Colors were very vivid and detail was high in some scenes. Overall we give Video quality a B.</p>

<p><br />
<strong>Audio</strong><br />
Audio was better than the video. We will focus our discussion on Dolby True HD or DTS MA only. There are standard Dolby Digital sound tracks as well. For the most part Close Encounters audio is dialog but there is plenty of content to put your system through its paces. In fact Ara's subwoofer has never been pushed as much as a few scenes in this movie did. This movie can be used to find objects in the room that will vibrate at extreme low frequency. The scene where Roy (Richard Dreyfuss) encounters the UFO at the Railroad crossing sounded incredible and can be thought of a room calibration sequence when played at high levels. If something in your theater is going to vibrate this sequence will bring it out. The other scene that makes great use of next generation audio is the UFO contact scene at the end of the movie. The sequence where the humans are communicating with the UFOs using tones was made for HD audio. The low frequency blast that breaks the glass in the movie had more impact than I remember in the movies. Great stuff! The audio is only 5.1 but wow do they get some audio out of it.</p>

<p>You can choose between Dolby True HD or DTS HD MA and they do sound different from each other. The thing we don't understand is that both formats are supposed to be a  bit-for-bit representation of the original movie's studio master soundtrack. So theoretically they should sound identical. Dolby Digital sounds tighter while DTS sound more open and airy. Both sound fantastic though. Over all we give Audio quality a B+. It would have been an A but some of the levels seemed off and dialog wasn't as bright as today's reference movies. We know, we know, the movie was shot in 1977 but still, we're just sayin'.</p>

<p><br />
<strong>Special Features</strong><br />
There are several special features but the one we enjoyed the most was something called "A View from Above" (available on the first disc). In this mode you could watch the Special Edition or Directors cut and symbols would show up on screen indicating if the scene was added or if a scene was deleted or changed. In the case of deleted scenes, a pop up would come on screen explaining what was removed. Other special features include:</p>

<ul><li>Steven Spielberg: 30 years of Close Encounters - Interview with Steven Spielberg (in HD, two channel audio)</li><li>The Making of Close Encounters (in SD)</li><li>Watch the Skies - (16:9, mix of HD and SD)</li><li>Deleted Scenes (Nine of them, letter boxed 4:3)</li><li>Explorations - concept drawings some split screened with final theatrical product, location pictures, marketing concepts, etc</li><li>Theatrical Trailers - in HD with 5.1 channel audio</li></ul>

<p><strong>Final Grade</strong> - A solid B! But still a great movie. Its just not what you'll use to show off your Home Theater.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>January  4, 2008 07:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 842
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

			<!-- Related Articles by Category -->
			<div class="item"><span class="corners-top"><span></span></span>
				<h2>More Blu-ray Movies Articles</h2>
				<ul class="brownsquare"></ul>
			<span class="corners-bottom"><span></span></span></div>
		
			<?if (8 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, entry_basename, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 8
					AND entry_id <> 842
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

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_id id, category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE entry_blog_id = 8
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY id, label
					ORDER BY label";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="../../category.php?category='. urlencode($category[label]) .'&id='. $category[id] .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/reviews/2008/01/close_encounters_of_the_third_kind.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
