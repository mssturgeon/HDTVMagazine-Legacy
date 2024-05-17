<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1276";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ara Derderian & Braden Russell'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1276 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

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
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
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
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
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
	<meta name="keywords" content="blu ray, high definition, beach ball, audio video, lossy compression, compression, video, mpeg, MPEG, much, HDTV, quality, hdtv, blu, ray, Blu, content, ball, beach, compress, high, looks, back, really, definition" />
	<meta name="description" content="If you're fairly new to the world of HDTV, one word you've probably heard a few times is 'compression.' Typically it's used in a very negative way, taking the blame for all sorts of picture quality issues. Unfortunately, compression gets a bad wrap most of the time, in fact, compression itself is really a great thing. In a nutshell, compression is exactly what it sounds like: taking something big and making it smaller, compressing it down to a smaller size. It's the thing that allows a full HDTV movie to fit on a Blu-ray disk, or for an HDTV program to be broadcast to your home for your viewing pleasure. Uncompressed high definition content is simply too large to be of any use to us consumers. Compression is the thing that allows you to store and transfer audio and video content that otherwise would be way too big.

Think of a beach ball..." />
	<title>HDTV Magazine Columns - Newbie's Corner - A Compression Primer</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/newbies_corner_-_a_compression_primer';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Newbie\'s Corner - A Compression Primer'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/columns/2008/02/newbies_corner_-_a_compression_primer.php";
		if ($author[img] != '' && 10 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ara Derderian & Braden Russell" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Newbie's Corner - A Compression Primer</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ara Derderian & Braden Russell</b><br />
				<?=$author_title?>
				Posted on <b>February 26, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Education & Books">Education & Books</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/02/newbies_corner_-_a_compression_primer.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/columns/2008/02/newbies_corner_-_a_compression_primer.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/columns/2008/02/newbies_corner_-_a_compression_primer.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/columns/2008/02/newbies_corner_-_a_compression_primer.php&amp;phase=2&amp;title=Newbie%27s%20Corner%20-%20A%20Compression%20Primer&amp;bodytext=If%20you%27re%20fairly%20new%20to%20the%20world%20of%20HDTV%2C%20one%20word%20you%27ve%20probably%20heard%20a%20few%20times%20is%20%27compression.%27%20Typically%20it%27s%20used%20in%20a%20very%20negative%20way%2C%20taking%20the%20blame%20for%20all%20sorts%20of%20picture%20quality%20issues.%20Unfortunately%2C%20compression%20gets%20a%20bad%20wrap%20most%20of%20the%20time%2C%20in%20fact%2C%20compression%20itself%20is%20really%20a%20great%20thing.%20In%20a%20nutshell%2C%20compression%20is%20exactly%20what%20it%20sounds%20like%3A%20taking%20something%20big%20and%20making%20it%20smaller%2C%20compressing%20it%20down%20to%20a%20smaller%20size.%20It%27s%20the%20thing%20that%20allows%20a%20full%20HDTV%20movie%20to%20fit%20on%20a%20Blu-ray%20disk%2C%20or%20for%20an%20HDTV%20program%20to%20be%20broadcast%20to%20your%20home%20for%20your%20viewing%20pleasure.%20Uncompressed%20high%20definition%20content%20is%20simply%20too%20large%20to%20be%20of%20any%20use%20to%20us%20consumers.%20Compression%20is%20the%20thing%20that%20allows%20you%20to%20store%20and%20transfer%20audio%20and%20video%20content%20that%20otherwise%20would%20be%20way%20too%20big.%0A%0AThink%20of%20a%20beach%20ball...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p>If you're fairly new to the world of HDTV, one word you've probably heard a few times is 'compression.' Typically it's used in a very negative way, taking the blame for all sorts of picture quality issues. Unfortunately, compression gets a bad wrap most of the time, in fact, compression itself is really a great thing. In a nutshell, compression is exactly what it sounds like: taking something big and making it smaller, compressing it down to a smaller size. It's the thing that allows a full HDTV movie to fit on a Blu-ray disk, or for an HDTV program to be broadcast to your home for your viewing pleasure. Uncompressed high definition content is simply too large to be of any use to us consumers. Compression is the thing that allows you to store and transfer audio and video content that otherwise would be way too big.</p>

<p>Think of a beach ball. It's a great device that provides hours of fun for all ages. It's the most fun when it's in the fully inflated state, but in that state it's very difficult to store or to transport. You need to be able to deflate (or compress) it to store it in the garage when it isn't in use, or put it in the car to take it to the beach or park, then re-inflate (or uncompress) it when it's time to use it. The same is true for audio or video content. You want to compress it to store it on a hard drive or disc media, or to transport it to a set top box or computer, then uncompress it to view it on a TV or computer screen.</p>

<p>Compression comes in two forms, lossless and lossy. Lossless compression allows you to compress the content all you want and when you decompress it, it will look exactly like it did before you squished it down. Thinking back to the beach ball analogy, every time you re-inflate the ball, it looks and functions exactly the same. Lossy compression doesn't quite work that way; when you compress the content, it permanently loses some quality. Then when you decompress it, it doesn't quite look as pristine as the original. In fact the more you compress it, the worse it gets when you decompress it. So if you take a little of the air out of the beach ball, when you blow it back up it looks almost perfect, but the more air you take out, the less perfect it looks when you blow it back up. If you compress it down to the smallest you can possibly get, by the time you re-inflate it, all you're left with is a faded, wrinkled mess that barely even resembles a beach ball. This is where compression gets its bad name. Too much compression can really destroy an audio or video file. A little compression goes a long way, but too much compression ruins the whole thing.</p>

<p>Why would anyone use lossy compression at all; it sounds like a really bad deal? Lossy compression does a much better job of shrinking file size without losing too much quality; the trade-off between size and quality is much better. Current HDTV compression got its roots from the Moving Picture Experts Group back in 1992 when they finalized the MPEG-1 codec. A codec (or compression/decompression algorithm) is the fancy math that's used to shrink the video file and then restore it back to its original glory. MPEG-1 was used on Video Discs and Video CDs. In 1994 MPEG-2 was born. It solved some of the shortcomings of MPEG-1 and added many new features. The main goal of MPEG-2 was to allow for much higher quality video. It was adopted for use on the DVD, in ATSC video broadcasts and is actually included in the spec for both Blu-ray and HD DVD. In fact, many of the early Blu-ray discs were encoded in MPEG-2.</p>

<p>But most of the modern video compression schemes have their roots in the next generation of MPEG, MPEG-4. It was finalized in the late 1990s and is rapidly becoming the codec of choice for HDTV via satellite, high definition movie downloads and on the next generation discs. You may have seen the terms H.264 or VC-1 out there. Both are derivatives of MPEG-4, in fact H.264 is just another name for MPEG-4 AVC (Advanced Video Coding). VC-1 is a Microsoft proprietary alternative to H.264. The reason MPEG-4 is so popular is that it allows the same quality playback at about half the file size of MPEG-2, as a general rule of thumb. Of course this better compression requires more computing power to decompress, but modern hardware has all but eliminated that concern.</p>

<p>Compression is often expressed in terms of bitrate, or how much data (bits) are used to render a second of information on the screen. Really good high definition broadcasts tend to be in the 18 Mbps (Mega-bits per second) range. When your HDTV content looks really bad, it's because the broadcaster may be compressing the image even more, possibly allowing for only 12, 10 or even 8 Mbps. You just can't re-inflate the beach ball once you've squeezed it down that small. Compare that to how good an HD DVD or Blu-ray movie looks. Blu-ray can go all the way up to 40 Mbps for video (54 Mbps total for audio and video). Now that's some high quality viewing.</p>

<p>The bottom line is that compression allows us to watch high definition video in our homes. Without it, a high definition movie would take up terabytes of storage space and would never fit on a Blu-ray or HD DVD disc and could never be saved on your DVR. Forget about even trying to broadcast an HDTV show to your house. So never complain about compression. But feel free to complain about over compression.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ara Derderian & Braden Russell</b>, <b>February 26, 2008 06:20 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1276
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
 			ORDER BY entry_created_on DESC LIMIT 25";
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
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 10
 				AND entry_id <> 1276
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

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Columns</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/columns/2008/02/newbies_corner_-_a_compression_primer.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
