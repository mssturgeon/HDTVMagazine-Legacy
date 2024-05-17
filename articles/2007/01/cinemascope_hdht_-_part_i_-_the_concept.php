<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 530";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 530 AND placement_is_primary = 1";
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
	<meta name="keywords" content="wide screen, black bars, cinemascope hdht, aspect ratio, screen movies, CinemaScope, image, cinemascope, screen, wide, bars, HDTV, black, hdtv, consumer, part, HDHT, aspect, resolution, hdht, project, ratio, articles, movie, could" />
	<meta name="description" content="Anyone can relate to how CinemaScope brings the movie experience to the eyes, some of us can even remember the Cinerama experience over 40 years ago, almost everyone is familiarized with the term and meaning of HDTV, and with HT (Home Theater).  So I created the term &quot;CinemaScope HDHT&quot; because it is actually blending the 3 concepts and technologies into one, and this series of articles is about implementing the 3 concepts, at your home, finally with consumer products.

For some people the choice of 16:9 (aspect ratio of 1.78:1 for HDTV) has been..." />
	<title>HDTV Magazine Articles - CinemaScope&#8482; HDHT - Part I - The Concept</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/cinemascope_hdht_-_part_i_-_the_concept';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('CinemaScope&#8482; HDHT - Part I - The Concept'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">CinemaScope&#8482; HDHT - Part I - The Concept</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>January 25, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php&amp;phase=2&amp;title=CinemaScope%26%238482%3B%20HDHT%20-%20Part%20I%20-%20The%20Concept&amp;bodytext=Anyone%20can%20relate%20to%20how%20CinemaScope%20brings%20the%20movie%20experience%20to%20the%20eyes%2C%20some%20of%20us%20can%20even%20remember%20the%20Cinerama%20experience%20over%2040%20years%20ago%2C%20almost%20everyone%20is%20familiarized%20with%20the%20term%20and%20meaning%20of%20HDTV%2C%20and%20with%20HT%20%28Home%20Theater%29.%20%20So%20I%20created%20the%20term%20%22CinemaScope%20HDHT%22%20because%20it%20is%20actually%20blending%20the%203%20concepts%20and%20technologies%20into%20one%2C%20and%20this%20series%20of%20articles%20is%20about%20implementing%20the%203%20concepts%2C%20at%20your%20home%2C%20finally%20with%20consumer%20products.%0A%0AFor%20some%20people%20the%20choice%20of%2016%3A9%20%28aspect%20ratio%20of%201.78%3A1%20for%20HDTV%29%20has%20been...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the CinemaScope&trade; series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/03/cinemascope_hdht_-_part_2.php">CinemaScope&#8482; HDHT - Part 2</a></li>
<li><a href="/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
Anyone can relate to how CinemaScope brings the movie experience to the eyes, some of us can even remember the Cinerama experience over 40 years ago, almost everyone is familiarized with the term and meaning of HDTV, and with HT (Home Theater).  So I created the term "CinemaScope HDHT" because it is actually blending the 3 concepts and technologies into one, and this series of articles is about implementing the 3 concepts, at your home, finally with consumer products.

<p>For some people the choice of 16:9 (aspect ratio of 1.78:1 for HDTV) has been cut short as aspect ratio and not wide enough to convey the typical feeling of wide-screen movies.  If the movie itself has been filmed in a wider aspect ratio, like most movies do at 2.35:1, it means that the 16:9 HDTV set would have to show the movie image with top/bottom black bars to compensate for its wider geometry.  </p>

<p>It also means that the image would occupy a smaller space within the 16:9 cabinet frame of the TV, giving the impression of missing something above and below.  </p>

<p>Actually, those black bars use part of (waste) the precious resolution of the media and of the TV (panel, chip, etc).  The movie image in between the bars ends up not as resolved as the Hi Def DVD media and the HDTV equipment make you believe they could be on their advertising schemes.  </p>

<p>That 1080p holy-grail TV you eagerly bought would actually end up using about 70% of its capable resolution for the actual 2.35:1 image, because the black bars use the other 30% of the available resolution for just black lines.  </p>

<p>As to the media side, the Hi Def DVD disc containing a wide-screen 2.35:1 movie would actually store those black bars in the disc above and below the image, wasting 30% of real state occupied by the video, and the player would play them back through your TV/projector as if they were part of the movie.</p>

<p>Black bars, top and bottom, a wide-screen image in a wide-screen TV and still bars, hmmm...</p>

<p>16:9 HDTV was supposed to be the way of the future, a wide-screen solution wider than the legacy 4:3 NTSC system, but wait, let us look at the other side.  We still tune to lots of programming recorded in 4:3, that when displayed on a 16:9 HDTV would have two pillar bars at each side to compensate for the different geometry of that "squarish" 4:3 image.</p>

<p>Although one can still stretch the 4:3 image to fill the 16:9 frame of the TV, some viewers dislike faces that become too fat, heads or feet cut by the image expansion, game scores that are usually down below in the image on an ESPN sport program become out of the viewing area due to the expansion, etc.  </p>

<p>Some TVs have scrolling capabilities to let you move up/down edges of the viewing area to bring back pieces of the image, like bringing up the server player on a tennis baseline at the bottom of the screen, but most sets do not have such capability, they only have a couple of image stretching modes, non-standard modes that the TV designer expects you to like, if you know what I mean. </p>

<p>Black bars again, now with pillars on the side, not much we could do about legacy but, hmmm... </p>

<p>People thought that we were getting rid of black bars when switching from 4:3 TVs to 16:9 HDTVs, especially to view wide-screen movies, but unfortunately is not quite that way.  </p>

<p>Black bars, on the four sides, and sometimes all at the same time within the same program, are here to stay for the long journey of our new system of 16:9 HDTVs, whether you have a TV or a panel, because all HDTV cabinets are cut as 16:9.  There has to be black bars because the physical aspect ratio of the TV cabinet cannot change to adapt to the aspect ratio of the media.  </p>

<p>So you think, there it goes my CinemaScope dream, not quite.</p>

<p>If you are looking for a front projection solution for your HT wide-screen movies and for your HDTV viewing, there is something you could now do for the wide-screen dream on CinemaScope.  </p>

<p><img src="/images/articles/cinemascope.jpg" alt="CinemaScope" align="left" />Using the concept of "constant height", which is maintaining the same image height of the screen for both 16:9 and 2.35:1 material, an electronic /optical solution can display a wider image by making wider the sides of your viewing area, just like some elegant local theaters still do when the main feature is about to start and roll the large curtains laterally to let open a much wider screen.  Get the popcorn now.    </p>

<p>That is exactly the subject of these articles, how to implement your own "CinemaScope HDHT (High Definition Home Theater)" for wide-screen movies in a HDTV HT projection environment.</p>

<p>Remember those emotions flowing when viewing that large wide-screen image at the local theater, and you were fully immersed in the action?  An image that excited the very wide angles of your horizontal vision in a way that no NTSC and no HDTV and no Hi def DVD could do, regardless of the resolution they might have, this about the effect of increasing aspect ratio, and increasing the angle of view, not just resolution.</p>

<p>CinemaScope HDHT certainly benefits with more resolution, but just having 720p, 1080i, or even 1080p would not do, they can only show more pixels, or show them faster to be more suitable to fast action on the image, but the geometric difference of most movies vs. the smaller aspect ratio of TV, NTSC or HDTV, can not be fully addressed by resolution increases alone.  Although some people use their existing projection systems with zoom and masking to adapt to those differences, the resolution lines are still lost to black lines, behind the masks, but lost. </p>

<p>Over the last few years a few companies have been working overtime to bring wide-screen CinemaScope movie viewing to consumers at home, and brought to market several technologies together, electronic and optical, and now relatively affordable to regular consumers looking for their own HT projection environments now that 1080p projectors have become so affordable.</p>

<p>However, there are still some areas that a designer/installer has to carefully address to make sure the pieces of a system fit with each other, because a wide-screen anamorphic lens manufacturer needs to work with the projector manufacturer, and both need a scaler manufacturer with a suitable product, and a lens transport manufacturer, installation hardware manufacturer, etc.  </p>

<p>Many of these companies are working hard to offer consumer-transparent harmonic multi-product solutions, and some projector manufacturers are even taking over the responsibility to deliver all the pieces manufactured by other vendors specially tailored to their projectors, but it is always a good idea to hire a local installer that knows about how to install a CinemaScope concept.  </p>

<p>One can always add to the project the non-electronics part of making that CinemaScope home-theater looking like a real theater, with columns, dark ceilings, seats, etc., and that is not within the scope of these articles.  The audio part is not covered, and is assumed the consumer would already have a multi-channel audio system.</p>

<p>Up until recently the coordination efforts of fitting the products was left to the creativity of individual home theater enthusiasts, people that had to perform various tailored solutions to make things work, some not designed to work together, no instructions in most cases, some were personal inventions of manually made anamorphic lens and other parts built for the pieces of the HT to fit well with the rest.  </p>

<p>Some magazines take one area, and entice you to immerse your efforts for a full project without necessarily telling the whole story, weak areas that could go wrong, and affect the rest of the project, and most generally the overall budget.</p>

<p>Advertising is easy, reaching the final objective and investing your own money in a real project and technical solution is not, and the regular consumer should be made aware of the roadblocks and the collateral expenses that a dream CinemaScope project could entail.  </p>

<p>No magazine covers the whole story, and you should be prepared before you start signing the first check.  One thing is the theory, another very different thing is actually doing it, and so I decided to be a Guinea Pig and invest my own money on this concept, and help our readers with the articles.</p>

<p>I decided to dedicate a new room for this experience and technical challenge to see what is possible for a regular consumer with current electronics and optics.  This is not about building and beatifying a HT with seats, columns, popcorn machines, curtains, star ceiling, that is easy, many companies do such projects, this is about how to get the CinemaScope video solution to work, you add your own audio.  </p>

<p>Over the last couple of months I contacted several manufacturers to work together with them, and get their latest products, some of them were prototypes.  I agreed with them that I would write these articles to tell the true story to the consumer and that they will collaborate in documenting the effort.  </p>

<p>I also contacted local dealers, installers, an ISF calibrator, etc. exactly the same way any consumer would have to do, this was not about using my experience and capabilities of doing the project, this was about doing the steps that a regular consumer would have to do, and paying the bills any consumer would have to pay, no discounts, no breaks, no freebies.  Just like other magazines do, don't they?  This is the only way these articles could be of value to you the consumer.</p>

<p>Stay tuned for Part II, and welcome to the beauty of CinemaScope HDHT.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>January 25, 2007 04:30 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 530
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
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
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
 				AND entry_id <> 530
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
