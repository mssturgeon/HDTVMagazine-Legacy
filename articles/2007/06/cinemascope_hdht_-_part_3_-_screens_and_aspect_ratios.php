<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 620";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 620 AND placement_is_primary = 1";
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
	<meta name="keywords" content="cinemascope system, aspect ratio, anamorphic lens, cinemascope hdht, aspect ratios, CinemaScope, cinemascope, screen, image, system, wider, viewing, aspect, resolution, using, ratio, same, theater, bars, projector, content, movies, home, scaler, might" />
	<meta name="description" content="If your HDTV has a fixed frame, such as an LCD/plasma panel or a rear-projection TV, and you want to see the CinemaScope&amp;trade; image at its intended aspect ratio, there is not much that can be done about the bars at the top and bottom of the screen. As a result, the bars use part of the valuable vertical resolution of the 16:9 TV or the projector's chip. To make things worse some technologies, such as LCD projection, show the black bars as dark gray, distracting the viewing of the actual image.

The HD chip of a front projector is fixed to the resolution of its design (720px1280 or 1080px1920 pixels). Many projectors today are chip-based DLP, LCD, and LCoS technologies at 720p resolution. More recently, several affordable 1080p projectors were introduced to the market." />
	<title>HDTV Magazine Articles - CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>June 27, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php&amp;phase=2&amp;title=CinemaScope%26trade%3B%20HDHT%20-%20Part%203%20-%20Screens%20and%20Aspect%20Ratios&amp;bodytext=If%20your%20HDTV%20has%20a%20fixed%20frame%2C%20such%20as%20an%20LCD%2Fplasma%20panel%20or%20a%20rear-projection%20TV%2C%20and%20you%20want%20to%20see%20the%20CinemaScope%26trade%3B%20image%20at%20its%20intended%20aspect%20ratio%2C%20there%20is%20not%20much%20that%20can%20be%20done%20about%20the%20bars%20at%20the%20top%20and%20bottom%20of%20the%20screen.%20As%20a%20result%2C%20the%20bars%20use%20part%20of%20the%20valuable%20vertical%20resolution%20of%20the%2016%3A9%20TV%20or%20the%20projector%27s%20chip.%20To%20make%20things%20worse%20some%20technologies%2C%20such%20as%20LCD%20projection%2C%20show%20the%20black%20bars%20as%20dark%20gray%2C%20distracting%20the%20viewing%20of%20the%20actual%20image.%0A%0AThe%20HD%20chip%20of%20a%20front%20projector%20is%20fixed%20to%20the%20resolution%20of%20its%20design%20%28720px1280%20or%201080px1920%20pixels%29.%20Many%20projectors%20today%20are%20chip-based%20DLP%2C%20LCD%2C%20and%20LCoS%20technologies%20at%20720p%20resolution.%20More%20recently%2C%20several%20affordable%201080p%20projectors%20were%20introduced%20to%20the%20market.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<li><a href="/articles/2007/01/cinemascope_hdht_-_part_i_-_the_concept.php">CinemaScope&#8482; HDHT - Part 1 - The Concept</a></li>
<li><a href="/articles/2007/03/cinemascope_hdht_-_part_2.php">CinemaScope&#8482; HDHT - Part 2</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
I use the word CinemaScope&trade; to convey the idea of any content with an aspect ratio that is wider than 1.78:1 (16:9 HDTV), which would be displayed with black top/bottom bars on any HDTV.

<p>If your HDTV has a fixed frame, such as an LCD/plasma panel or a rear-projection TV, and you want to see the CinemaScope&trade; image at its intended aspect ratio, there is not much that can be done about the bars at the top and bottom of the screen. As a result, the bars use part of the valuable vertical resolution of the 16:9 TV or the projector's chip. To make things worse some technologies, such as LCD projection, show the black bars as dark gray, distracting the viewing of the actual image.</p>

<p>The HD chip of a front projector is fixed to the resolution of its design (720px1280 or 1080px1920 pixels). Many projectors today are chip-based DLP, LCD, and LCoS technologies at 720p resolution. More recently, several affordable 1080p projectors were introduced to the market.</p>

<p>Regardless of the projector's resolution, there will be a loss of about 30% of the vertical resolution (measured top to bottom) when displaying the blacks bars of a wider CinemaScope&trade; letterboxed movie without using a CinemaScope&trade; system.</p>

<p>However, something can be done with front projectors to use the full resolution of the chip when displaying CinemaScope&trade; images in wider screens; A CinemaScope&trade; system with anamorphic lens, capable scaler, and a 2.35:1 screen can be implemented.</p>

<p>For some Home Theater enthusiasts, retaining that resolution and viewing 2.35:1 movies in 2.35:1 screens is sufficient to pursue a full CinemaScope&trade; system.</p>

<p><img src="/images/articles/aspect-comparison.jpg" alt="Aspect Ratio Comparison" /></p>

<p><b>CinemaScope&trade; Using a 16:9 Screen</b></p>

<p>As mentioned before, a 2.35:1 screen would be ideal for showing a 2.35:1 CinemaScope&trade; movie, and, when displaying 16:9 images, the CinemaScope&trade; system would maintain the same "Constant Height" using the same 2.35:1 screen.</p>

<p>However, what happens if one has an existing 16:9 screen and wants to use it also to display wider content of 2.35:1 aspect ratio? Although these articles are not intended to cover this particular scenario in depth, allow me to offer some brief comments about this subject.</p>

<p>While it is possible to view wider CinemaScope&trade; content on a 16:9 projection screen (using or not a CinemaScope&trade; scaler and anamorphic lens) we know that the image width will be limited by the width of the 16:9 projection screen, which would make the 2.35:1 CinemaScope&trade; image shorter with top/bottom black bars, and the objects on it appear relatively smaller than 16:9 material displayed on the same 16:9 screen.</p>

<p>A CinemaScope&trade; system, regardless of the aspect ratio of the screen, requires a scaler adding approximately 30% interpolated horizontal lines stretching the image vertically to use the full 720 or 1080 vertical resolution of the projector's chip (making objects look thinner and taller), then the anamorphic lens would stretch the image horizontally to restore its original geometry.</p>

<p>Some home theater enthusiasts migrating to a 2.35:1 screen installation (and those not migrating as well) might find it tempting to start using a CinemaScope&trade; system with an existing 16:9 screen under the theory that maximizing the projector's vertical resolution with the scaler would provide a better image than the original at the same size.</p>

<p>The assumption of "more lines/pixels should render a better image" might sound tempting on the surface, but those are not pixels of original resolution, those are "scaler magic". What happens when your magician is not perfect?</p>

<p>Depending on the CinemaScope&trade; system's electronic and optical quality, the manipulation on the four axes of the image could negatively affect the quality of the original image that had fewer pixels but were pixel-perfect-fit from source to display.</p>

<p>In other words, it might be better to display the image as it is, and accept that a 16:9 screen will not accommodate for a wider image, which is actually the main objective of a CinemaScope&trade; system; displaying a wide-image wider without making it shorter.</p>

<p>Additionally, the approach of using a 16:9 screen for CinemaScope&trade; images is not considered "Constant Height" until a 2.35:1 screen is used to complete the CinemaScope&trade; system, and that is where the net gain in benefits is to be found.</p>

<p>Although it is a matter of personal choice to start with these components of the CinemaScope&trade; system using an existing 16:9 screen and get the 2.35:1 screen later, one should be concerned with ruining a pixel perfect image just by assuming that 30% more interpolated lines to use the projector chip's full resolution would be an improvement on the same size of image.</p>

<p>If a CinemaScope&trade; system would not be used for a 16:9 screen, why would Home Theater people use a 16:9 screen for images of wider aspect ratios? Many Home Theater rooms have limited left/right space and have installed 16:9 screens to maximize the overall image size of more frequent viewing of 16:9 and 4:3 content. Those people do not mind occasionally viewing a 2.35:1 image relatively smaller than 16:9 content, more on this later.</p>

<p><br />
<b>The Wider Formats</b></p>

<p>Many modern movies are filmed/transferred as 2.35, 1.85, 1.78, 2.40, 2.39, etc. According to some publications that show aspect ratio information, such as Widescreen Review Magazine, in a recent 5 month period 50-60% of the reviewed movies were at or wider than 2.35:1, but mostly 2.35:1.</p>

<p>Most of the newer Hi-Def DVDs are being released at 2.35:1 and wider. Blu-ray in December 2006 introduced five 2.35:1 movies vs. three of smaller aspect ratios, in January introduced six vs. three, and in February introduced six vs. two. The HD DVD format on the same period was about half and half.</p>

<p>It would be interesting to also analyze the timing and implementation tendency of wider formats for filmmaking, to determine if there is a gradual shift toward the use of wider formats in a 16:9 HDTV era.</p>

<p>Regarding regular DVD releases, during 2 weeks in December 2006, 15 titles were 2.35:1 and wider, three were listed as TBD, and nine titles were not wider than 2.35:1.</p>

<p>Historically, movie theater film shifted to wider aspect ratios when NTSC TV was introduced over 50 years ago with a similar 4:3 aspect ratio, to offer a theatrical format that could not be viewed on a TV at home, and keep the public interested in attending the local movie theater.</p>

<p>Now that TV is 16:9, Hollywood might be inclined to use wider than HD formats more frequently to attract the public to the local movie theater with widescreen movies viewed without black bars. The movie-making industry must be maintained alive and the art supported, regardless of what TV system is in place. Everyone needs this art in one way or another.</p>

<p>Some directors select the 16:9 aspect ratio so their movies can be viewed on an HDTV at home exactly the same way as they are viewed in the theater, with no black-bars.</p>

<p>This series of articles is not intended to cover the aspect ratio differences and their implementation in filmmaking. For that subject, I recommend reading some articles published over the years by the Widescreen Review Magazine.</p>

<p>If CinemaScope&trade; movies are increasingly released, it will be beneficial to have a home theater system capable of maximizing the resolution of 16:9 projectors for CinemaScope&trade; viewing by using anamorphic lens and enabled scalers.</p>

<p><br />
<b>The Viewing Position Using a CinemaScope&trade; System with 2.35:1 Screens</b></p>

<p>When using a 2.35:1 screen with a CinemaScope&trade; system implementing "Constant Height", one should determine sound and viewing positions based on 16:9 viewing. The scaler would make 2.35:1 viewing maintain the same image/screen height of a 16:9 image. The anamorphic lens expands the image laterally to cover the entire width of the 2.35:1 screen, increasing the angle of view and the cinematic impact.</p>

<p>There is no need to change viewing positions or audio sweet spots when changing aspect ratios, and no need to adjust the zoom, focus, etc. The system should be seamless. Some systems even operate by having the scaler auto-detect the aspect ratio of the content, switch the aspect ratio automatically, and issue control commands to automatically move the anamorphic lens/sled in/out of the path of the projector, move screen masks to adapt to the image, roll curtains, etc.</p>

<p>As a viewer, you just select the content and let the system work for you.</p>

<p><br />
<b>The Viewing Position Using a 16:9 Screen Also for CinemaScope&trade; Viewing</b></p>

<p>On the other hand, when using a 16:9 screen and no system, there is always the alternative of doing nothing, waste some of the projector's resolution and light output to display 30% of black bars, and learn to live with a shorter 2.35:1 image on a 16:9 screen. Such approach might prompt you to move the viewing position closer to the screen to adjust to the smaller 2.35:1 image to receive a cinematic impact.</p>

<p>While the lateral angle of vision did not increase, having a shorter image makes objects within it proportionally smaller than the same objects within a taller 16:9 image that uses the full area of the screen, both having the same width. For that reason, when switching back to view larger 16:9 images you might want to move the seating position further from the screen.</p>

<p>Switching viewing positions to adjust to the aspect ratio of the image could not only be inconvenient, but could also potentially affect the sweet spot of multi-channel audio when it was calibrated for one viewing position, while is unacceptable for the other.</p>

<p>It is impractical to change the speaker's location to adjust the multi-channel audio sweet spot as you switch viewing-positions, and it is certainly an impossible option if the speakers are mounted in the walls/ceilings.</p>

<p>Stay tuned for the next article in this CinemaScope&trade; HDHT series.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>June 27, 2007 03:23 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 620
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
 				AND entry_id <> 620
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
