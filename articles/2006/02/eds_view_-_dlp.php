<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 329";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 329 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
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
	<meta name="keywords" content="color wheel, consumer electronics, primary color, texas instruments, lens system, DLP, dlp, color, light, pixel, chip, system, image, projection, wheel, figure, Figure, method, primary, mirror, projected, electronics, used, time, systems" />
	<meta name="description" content="I first saw a DLP (Digital Light Processor)* demonstration in 1989 at a Society for Information Display (SID) conference in San Jose. The domo was given by Texas Instruments, Inc., the inventor of the DLP.  Demonstrations at SID conferences represent the very cutting edges of new display technologies. Therefore, the demos are comparatively crude exhibitions of concept prototypes.  However, in this instance the DLP demo was particularly crude.  It exhibited a comparatively dim, low-resolution projected monochrome image with several black areas caused by &quot;stuck&quot; pixels.  Of all the prototypes shown, DLP seemed to have the least promise.  A few years later, at the urging of Dr. Jim Carnes, then Director of the David Sarnoff Research Center, I traveled to Washington, DC, to see another DLP demo at a Defense Advanced Research Projects Agency (DARPA) conference" />
	<title>HDTV Magazine Articles - Ed's View - DLP</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_dlp';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - DLP'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/02/eds_view_-_dlp.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - DLP</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>February  6, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/02/eds_view_-_dlp.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/02/eds_view_-_dlp.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/02/eds_view_-_dlp.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/02/eds_view_-_dlp.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20DLP&amp;bodytext=I%20first%20saw%20a%20DLP%20%28Digital%20Light%20Processor%29%2A%20demonstration%20in%201989%20at%20a%20Society%20for%20Information%20Display%20%28SID%29%20conference%20in%20San%20Jose.%20The%20domo%20was%20given%20by%20Texas%20Instruments%2C%20Inc.%2C%20the%20inventor%20of%20the%20DLP.%20%20Demonstrations%20at%20SID%20conferences%20represent%20the%20very%20cutting%20edges%20of%20new%20display%20technologies.%20Therefore%2C%20the%20demos%20are%20comparatively%20crude%20exhibitions%20of%20concept%20prototypes.%20%20However%2C%20in%20this%20instance%20the%20DLP%20demo%20was%20particularly%20crude.%20%20It%20exhibited%20a%20comparatively%20dim%2C%20low-resolution%20projected%20monochrome%20image%20with%20several%20black%20areas%20caused%20by%20%22stuck%22%20pixels.%20%20Of%20all%20the%20prototypes%20shown%2C%20DLP%20seemed%20to%20have%20the%20least%20promise.%20%20A%20few%20years%20later%2C%20at%20the%20urging%20of%20Dr.%20Jim%20Carnes%2C%20then%20Director%20of%20the%20David%20Sarnoff%20Research%20Center%2C%20I%20traveled%20to%20Washington%2C%20DC%2C%20to%20see%20another%20DLP%20demo%20at%20a%20Defense%20Advanced%20Research%20Projects%20Agency%20%28DARPA%29%20conference&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Ed's view</p>

<p>DLP</p>

<p>I first saw a DLP (Digital Light Processor)* demonstration in 1989 at a Society for Information Display (SID) conference in San Jose. The <img alt="dlpfig1.jpg" src="http://www.hdtvmagazine.com/articles/Images/dlpfig1.jpg" width="250" height="206" align="left"/>domo was given by Texas Instruments, Inc., the inventor of the DLP.  Demonstrations at SID conferences represent the very cutting edges of new display technologies. Therefore, the demos are comparatively crude exhibitions of concept prototypes.  However, in this instance the DLP demo was particularly crude.  It exhibited a comparatively dim, low-resolution projected monochrome image with several black areas caused by "stuck" pixels.  Of all the prototypes shown, DLP seemed to have the least promise. A few years later, at the urging of Dr. Jim Carnes, then Director of the David Sarnoff Research Center, I traveled to Washington, DC, to see another DLP demo at a Defense Advanced Research Projects Agency (DARPA) conference.  What I saw was now greatly improved.  The image was larger, brighter and in color.  Although there were still a number of "stuck" pixels and a slightly misconverged image, the three chip system now showed great promise as a possible consumer electronics product, albeit very expensive.  Subsequent visits to Texas Instruments in Dallas indicated an ever-increasing level of performance.</p>

<p>Today, DLP is one of the prime projection technologies used for HDTV.  Many major manufacturers have adopted DLP for most or all of their HDTV projection models.  And no wander, DLP delivers a very cost effective, bright, high-quality HDTV image using an elegant, simple projection system design.  Here is a brief review of how it works:</p>

<p><br />
<strong>Figure 1</strong> is a photograph of a mounted HDTV DLP chip.  This chip consists of over two million individual pixels plus driving electronics. A photomicrograph <strong>(Figure 1A)</strong> of the chip surface shows the actual pixel array. <img alt="dlpfig1A.jpg" src="http://www.hdtvmagazine.com/articles/Images/dlpfig1A.jpg" width="300" height="300" align="right"/></p>

<p>Each pixel consists of a tiny movable mirror, which flips back and forth upon application of a very small voltage provided by the driving electronics.  </p>

<p><strong>Figure 2</strong> illustrated the anatomy of a DLP pixel.  The moving mirror is mounted on a hinge that allows it to pivot approximately ±10&deg; upon application of the "address" voltage. This voltage is applied between the mirror surface and one of two electrodes located on either side of the pixel base.   When the address voltage is applied to either one of the base electrodes, the static charge generated causes the mirror to pivot toward the respective address electrode.  Figure 2 illustrates the mirror pivoted in a position that reflects light away from an associated lens system that projects the light to the screen.  Thus, this pixel's reflected light does not appear on the screen, generating a projected "black" pixel. </p>

<p><img alt="dlpfig2.jpg" src="http://www.hdtvmagazine.com/articles/Images/dlpfig2.jpg" width="240" height="116"/></p>

<p>When the pixel is flipped to the opposite position <strong>(Figure 3</strong>), light is directed into the projection lens system, generation a "white" pixel.  This bi-stable light switching action allows the image to be generated digitally.  By varying the amount of time each pixel reflects light into the lens system (the "on" time), the brightness of the pixel's reflected light can be varied, thus creating a varying gray scale, i.e. the less time the pixel is "on," the darker the reflected pixel, and the more time it is "on," the brighter the pixel. This digital addressing scheme is very similar to the driving process used with plasma technology. (See my previous article, "Plasma.")</p>

<p><br />
<img alt="dlpfig3.jpg" src="http://www.hdtvmagazine.com/articles/Images/dlpfig3.jpg" width="240" height="118" /></p>

<p>There are three methods today's DLP systems can generate a color image. One method is to use three separate chips, one for each primary color. The incoming light is split into the primary colors (Red, Green and Blue), modulated by the respective chip, and converged in the projection optics. Since the light reflecting efficiency of the DLP mirrors is very high, the resulting projected image from a three-chip system can be very bright. This solution, however, is relatively expensive, but is successfully employed in commercial front projection applications.</p>

<p> <br />
<img alt="dlpfig4.jpg" src="http://www.hdtvmagazine.com/articles/Images/dlpfig4.jpg" width="170" height="217"align="left"/><br />
The second method is to employ a rotation color filter (wheel) with a single DLP chip.  The filter wheel itself <strong>(Figure 4)</strong>, consists of a disk segmented into the three primary colors, plus some additional color "mixes" to increase the projected color gamut.  The primary color components of each video frame sequentially modulate the DLP chip in synchronization with the respective light color passed by the rotating color wheel. The result is a full color image projected onto the screen <strong>(Figure 5)</strong>.<br />
[<u>Historic note</u>: This technique is actually a modern adaptation of the CBS color wheel scheme first used in early 1950's but later replaced by the all electronic RCA  tri-color system.]</p>

<p><img alt="dlpfig5.jpg" src="http://www.hdtvmagazine.com/articles/Images/dlpfig5.jpg" width="256" height="215"align="right"/></p>

<p>The color wheel method is the one used today in consumer electronics applications of the DLP technology, as well as in some less expensive commercial front projection systems. Using one chip with a color wheel allows a very cost effective, low weight projection "light engine," with the added advantage of eliminating the complexity and cost of optical color convergence.  </p>

<p>The third method is new, having been first publicly demonstrated at the 2006 CES.  This method eliminates the mechanical color wheel by utilizing switched Red, Green, and Blue LEDs (Light Emitting Diodes) to generate a sequential primary color light source.  This system allows an all-electronic solution with all the advantages of the color wheel method. It can be anticipated that soon all DLP systems with employ LED's.</p>

<p>The primary DLP competitors for consumer electronics applications are LCD and LCoS technologies, both of which will improve performance by the use of LED's.  From a performance standpoint there is parity among these technologies.  At the end of the day, the winner will be decided by systems cost.  And that's a good thing.</p>

<p>Ed</p>

<p>*Also known as DMD - Digital Micromirror Device</p>

<p>(Illustrations courtesy of Texas Instruments Inc.)</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>February  6, 2006 10:39 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 329
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 329
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
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
 				<h2>About Ed Milbourn</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/eds_view_-_dlp.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
