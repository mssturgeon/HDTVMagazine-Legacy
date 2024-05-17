<?
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 924";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ara Derderian & Braden Russell'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 924 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $GOOGLE_CHANNEL['Ara Derderian & Braden Russell'];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (10) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			break;
		case 7: # Bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
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
	<meta name="keywords" content="aspect ratio, wide screen, linear pcm, dvd player, aspect ratios, aspect, PCM, pcm, ratio, screen, data, audio, movies, movie, Aspect, wide, receiver, digital, player, bitstream, DVD, HDTV, dvd, Ratio, hdtv" />
	<meta name="description" content="So you went out and bought yourself a new HDTV. Many of you bought a sound system to go with it. Now you are inundated with all kinds of terms and acronyms. In this series we will go through the jargon associated with your new system. While these articles are aimed at newbies, some experienced readers out there may learn a thing or two as well. Today we start with two topics that come up on our podcast (&lt;a href=&quot;http://www.htguys.com&quot;&gt;HDTV and Home Theater Podcast&lt;/a&gt;) quite frequently...." />
	<title>HDTV Magazine Columns - PCM, LPCM, Bitstream, and Aspect Ratio Explained</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
#		$pagetag = "NTPT_PGEXTRA = 'author=". $AUTHOR_ID['Ara Derderian & Braden Russell'] ."';\n";
#		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/pcm_lpcm_bitstream_and_aspect_ratio_explained';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('PCM, LPCM, Bitstream, and Aspect Ratio Explained'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/columns/2008/01/pcm_lpcm_bitstream_and_aspect_ratio_explained.php";
		if (array_key_exists('Ara Derderian & Braden Russell', $AUTHOR_PORTRAIT) && 10 <> 7) {
			$img = '<img src="'. $AUTHOR_PORTRAIT['Ara Derderian & Braden Russell'] .'" alt="Ara Derderian & Braden Russell" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">PCM, LPCM, Bitstream, and Aspect Ratio Explained</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ara Derderian & Braden Russell</b><br />
				<?=$author_title?>
				Posted on <b>January 29, 2008</b><br />
				Category: <b><a href="/category.php?category=Education & Books&id=<?=$category_id?>">Education & Books</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/01/pcm_lpcm_bitstream_and_aspect_ratio_explained.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/columns/2008/01/pcm_lpcm_bitstream_and_aspect_ratio_explained.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/columns/2008/01/pcm_lpcm_bitstream_and_aspect_ratio_explained.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & $sub_type) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/01/pcm_lpcm_bitstream_and_aspect_ratio_explained.php&amp;phase=2&amp;title=PCM%2C%20LPCM%2C%20Bitstream%2C%20and%20Aspect%20Ratio%20Explained&amp;bodytext=So%20you%20went%20out%20and%20bought%20yourself%20a%20new%20HDTV.%20Many%20of%20you%20bought%20a%20sound%20system%20to%20go%20with%20it.%20Now%20you%20are%20inundated%20with%20all%20kinds%20of%20terms%20and%20acronyms.%20In%20this%20series%20we%20will%20go%20through%20the%20jargon%20associated%20with%20your%20new%20system.%20While%20these%20articles%20are%20aimed%20at%20newbies%2C%20some%20experienced%20readers%20out%20there%20may%20learn%20a%20thing%20or%20two%20as%20well.%20Today%20we%20start%20with%20two%20topics%20that%20come%20up%20on%20our%20podcast%20%28%3Ca%20href%3D%22http%3A%2F%2Fwww.htguys.com%22%3EHDTV%20and%20Home%20Theater%20Podcast%3C%2Fa%3E%29%20quite%20frequently....&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>So you went out and bought yourself a new HDTV. Many of you bought a sound system to go with it. Now you are inundated with all kinds of terms and acronyms. In this series we will go through the jargon associated with your new system. While these articles are aimed at newbies, some experienced readers out there may learn a thing or two as well. Today we start with two topics that come up on our podcast ( <a href="http://www.htguys.com">HDTV and Home Theater Podcast</a>) quite frequently. </p>

<p><strong>PCM, LPCM, and Bitstream</strong></p>

<p>Your DVD player has options for how it sends data to the receiver. You'll see terms like PCM, Linear PCM and Bitstream. Let's define these terms and explain when to use them.</p>

<p><em>Pulse Coded Modulation (PCM)</em>. This is standard method of encoding analog audio signals in digital form. The process involves taking samples of the audio from 8 to 192 thousand times per second (8 to 192kHz) and recording each sample as a digital number from 8 to 24 bits long. </p>

<p>For our discussion PCM data are raw digital audio samples. You'll want to use this if you have a next generation disc player like Blu-ray or HD DVD that supports Dolby True HD or DTS HD audio. In this case the player reads the raw data that was recorded onto the disc and decodes it into the sounds that you hear. Then it sends a PCM stream of audio data to the receiver that the receiver routes to the appropriate speakers. When the receiver reads PCM data no further processing is done. </p>

<p>PCM allocates more weight to more important frequency ranges. That is to say, that the frequencies that the human ear can easily hear are given more data and thus higher quality.</p>

<p>Set your disc player to output PCM data if the movie you are watching has a Dolby True HD, DTS HD Audio, or PCM sound track.</p>

<p><em>Linear PCM</em> - Similar to PCM, but Linear PCM uses data that is spread evenly across the frequency range. Each frequency is given equal weight. Some argue that this wastes bandwidth on frequencies that have little impact on the human ear.</p>

<p><em>BitStream</em> - This is the digital form of multiple channel audio data before it is decoded into its various channels. If your receiver is decoding a Dolby Digital or DTS signal from your DVD player it is reading a bitstream. If you have a standard DVD player with a digital connection, this is what is being output to your receiver. This is the default setting on almost all standard DVDs sold to date.</p>

<p><strong>Aspect Ratio</strong></p>

<p>The second topic for discussion is Aspect Ratio. We'll go through the some of the basics for newbies and maybe even throw in something for our more experienced readers. </p>

<p>Aspect Ratio defined - The ratio of width to height of a screen. Analog TV in the US and most of the world is 4:3. That means for every four units wide a TV is, it is 3 units high. For HDTV, the aspect ratio is 16:9 which means for every 16 units wide the TV is, it is 9 units high.</p>

<p>So why was HDTV chosen to be 16:9 and how come I still have black bars on my DVDs?</p>

<p>Cinematic movies are not shot in 16:9 (or 1.78:1) so why did the HDTV specification chose this aspect ratio? Note - when talking about TV the aspect ratio is given in either 4:3 or 16:9, when talking about cinematic films the aspect ratio is obtained by dividing the width by the height. So when you read a DVDs information and see that its aspect ratio is 1.78:1 you know it will fill your entire screen. If you see 1.33:1 then you know its a 4:3 or Full Screen movie.</p>

<p>The reason 16:9 was chosen was that when you take all the cinematic aspect ratios and lay them on top of each other 16:9 turned out to be the best compromise. It minimized the amount of unused screen when all aspect ratios were considered. As a result your new wide screen TV could end up with black bars at the top and bottom. But these black bars are much less than what you would see on your old 4:3 TV.</p>

<p>But some movies are not shot in 1.78:1 and yet they fill up the entire screen on my new Plasma TV. This happens when some studios master the DVD for wide screen TVs. They will actually crop the film to fit perfectly on your screen. This bothers many film aficionados who prefer watching movies in their Original Aspect Ratio (OAR). Some DVDs will have this acronym contained in the movie details to help consumers quickly identify that the movie is not cropped. Some pay movie channels will also crop the movie and this has drawn the ire of many. Yet others say, I paid for all that screen real estate so I want the pixels doing something.</p>

<p><em>Common Aspect ratios and their uses</em></p>

<p>CinemaScope - 2.35:1 or (~14:6) - Developed by 20th Century Fox, which is the only studio still using it. The original Star Wars Movies were filmed in CinemaScope</p>

<p>Panavision - 2.40:1 or (12:5) - Developed by Panavision and became very popular in the 1970s. Today they make lenses for movies with a 1.85 or (18.5:10) aspect ratio.</p>

<p>Academy - 1.33:1 or (4:3) All movies were actually 4:3. When TVs came on the scene they were designed to this specification. Movies then moved to wide screen to differentiate themselves from television. The Robe was the first movie to be filmed in wide screen.</p>

<p>Other film formats include Todd-AO, 2.2:1 (Oklahoma in 1955), Metroscope, 2:1 (Dirty Dozen), Cinerama, 2.8:1, (How the West was Won 1962), and there are others that that you can find out about by following this link (<a href="http://en.wikipedia.org/wiki/Aspect_ratio_(image)">http://en.wikipedia.org/wiki/Aspect_ratio_(image)</a> ).</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ara Derderian & Braden Russell</b>, <b>January 29, 2008 10:39 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 924
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
				<h2>More on Education & Books</h2>
   				<ul class="brownsquare"></ul>
			<span class="corners-bottom"><span></span></span></div>
		
			<div class="item"><span class="corners-top"><span></span></span>
				<h2>More on Education & Books</h2><ul><?
				$sql = "
				SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
				FROM mt_entry e, mt_author a, mt_placement p, mt_category c
				WHERE entry_author_id = author_id
					AND e.entry_id = p.placement_entry_id
					AND p.placement_category_id = c.category_id
					AND category_label = 'Education & Books'
					AND e.entry_status = 2
					AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
					AND entry_author_id = a.author_id
				ORDER BY entry_created_on DESC";
				$result = mQuery($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$ts = strtotime($row[entry_created_on]);
					$y = date('Y', $ts);
					$m = date('m', $ts);
					$entry = getEntryInfo($row[entry_blog_id]);
		
					$entry[date] = getDateString($ts);
					$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
					$entry[title] = $row[entry_title];
					$entry[author] = $row[author_name];

					echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
				}
			?></ul><span class="corners-bottom"><span></span></span></div>
			
			<?if (10 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, entry_basename, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 10
					AND entry_id <> 924
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'Ara Derderian & Braden Russell'
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($qry);
				
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					echo '<div class="item"><span class="corners-top"><span></span></span>'.
					'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
					<h2>About Ara Derderian & Braden Russell</h2>
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
					WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$res_authors = mQuery($qry);
					while ($row_authors = mysql_fetch_assoc($res_authors)) {
						echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

			<div class="item"><span class="corners-top"><span></span></span>
				<h2>Categories</h2>
				<ul class="brownsquare"><?
					$qry = "
					SELECT category_label label, COUNT(*) num
					FROM mt_entry e, mt_placement p, mt_category c
					WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
						AND entry_status = 2
						AND entry_id = p.placement_entry_id
						AND p.placement_category_id = c.category_id
					GROUP BY label
					ORDER BY label";
					$result = mQuery($qry);
					while ($category = mysql_fetch_assoc($result)) {
						echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/01/pcm_lpcm_bitstream_and_aspect_ratio_explained.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
