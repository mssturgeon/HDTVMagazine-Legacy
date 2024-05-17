<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 557";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 557 AND placement_is_primary = 1";
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
	<meta name="keywords" content="aspect ratio, anamorphic lens, home theater, wide screen, top bottom, screen, CinemaScope, cinemascope, projector, image, lens, system, resolution, aspect, content, could, masking, project, anamorphic, ratio, installation, home, movie, theater, bars" />
	<meta name="description" content="As I mentioned on the first article, I decided to launch this CinemaScope&amp;trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&amp;trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.

Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell..." />
	<title>HDTV Magazine Articles - CinemaScope&#8482; HDHT - Part 2</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/cinemascope_hdht_-_part_2';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('CinemaScope&#8482; HDHT - Part 2'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/03/cinemascope_hdht_-_part_2.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">CinemaScope&#8482; HDHT - Part 2</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>March  6, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/03/cinemascope_hdht_-_part_2.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/03/cinemascope_hdht_-_part_2.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/03/cinemascope_hdht_-_part_2.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/03/cinemascope_hdht_-_part_2.php&amp;phase=2&amp;title=CinemaScope%26%238482%3B%20HDHT%20-%20Part%202&amp;bodytext=As%20I%20mentioned%20on%20the%20first%20article%2C%20I%20decided%20to%20launch%20this%20CinemaScope%26trade%3B%20project%20at%20my%20own%20cost%20and%20with%20my%20own%20design%20and%20equipment%20selection%2C%20to%20been%20able%20to%20publish%20a%20series%20of%20articles%20for%20the%20readership%20of%20our%20magazine%2C%20and%20to%20show%20consumers%20that%20this%20concept%20of%20a%20high-definition%20home-theater%20%28HDHT%29%20CinemaScope%26trade%3B%20is%20economically%20affordable%2C%20technically%20possible%2C%20and%20could%20certainly%20be%20attractive%20to%20many%202.35%3A1%20movie%20viewers.%0A%0AHaving%20completed%20the%20electronic%20and%20optical%20stages%20of%20the%20system%20in%20a%20dedicated%20room%20with%20controlled%20lighting%2C%20I%20can%20tell...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<li><a href="/articles/2007/06/cinemascope_hdht_-_part_3_-_screens_and_aspect_ratios.php">CinemaScope&trade; HDHT - Part 3 - Screens and Aspect Ratios</a></li>
<li><a href="/articles/2007/09/cinemascope_hdht_part_4_-_budgeting_for_the_project.php">CinemaScope&trade; HDHT - Part 4 - Budgeting for the Project</a></li>
</ul></div>
<br />
First of all, forgive me for not been able to produce these articles as often as I had planned, I am very occupied with the annual report about HDTV Technology that I produce every year (for 5 years already) at this time and that has priority, but I will try to keep the momentum of these articles as warm as possible.

<p><br />
<B>The CinemaScope&trade; Project</B></p>

<p>As I mentioned on the first article, I decided to launch this CinemaScope&trade; project at my own cost and with my own design and equipment selection, to been able to publish a series of articles for the readership of our magazine, and to show consumers that this concept of a high-definition home-theater (HDHT) CinemaScope&trade; is economically affordable, technically possible, and could certainly be attractive to many 2.35:1 movie viewers.</p>

<p>Having completed the electronic and optical stages of the system in a dedicated room with controlled lighting, I can tell that the CinemaScope&trade; experience is breathtaking. Building this system was a challenge, but it was worth every penny and every minute invested on it.</p>

<p>The project is not about beautifying a home theater, it is about what is possible with quality electronics and optics to optimize the viewing of wide-screen content and to recreate the CinemaScope&trade; feeling of the classical theater.</p>

<p>It was also about simulating the steps a regular consumer would have to follow to make a similar project for their homes. Regardless if I was capable to install, connect, calibrate, etc., I decided to hire out the necessary labor for each task, rather than shaving costs by doing the job myself. The value of the project to readers was for me to do exactly what a regular consumer would have to do. Due to the fact that I intended to live with the system after it was finished, I designed the system and selected all the components myself. A consumer without such knowledge should expect the dealer/installer of the system to take that role.</p>

<p><br />
<img src="/images/articles/hdht-projector.jpg" alt="Optoma Projector" align="left" /><B>The Projector Took the Driver Seat</B></p>

<p>As you might know already, front-projector manufacturers are doing partnerships with manufacturers of other CinemaScope&trade; components, such as anamorphic lens, transports, plates, etc. They have improved their compatibility, and they are easier to install because they are made with specifications to fit with each other. They are sold by projector manufacturers as CinemaScope&trade; system packages, which make the whole project considerably smoother and cheaper to the consumer, particularly in labor costs. Not to mention the risks one would run by choosing components based on their individual merits that might end up not fitting as smoothly with each other.</p>

<p>This year we will see a growing number of manufacturers offering CinemaScope&trade; lens/transport package solutions bundled with projectors, and offering the lens/plate/transport in a separate package deal for those consumers that have a projector already.</p>

<p><br />
<B>Other Aspect Ratios Displayed on a 2.35:1 Screen</B></p>

<p>As I mentioned in the first article, the CinemaScope&trade; approach I am covering on these articles is not about simply zooming and/or masking 2.35:1 images on a screen. Is about implementing a 2.35:1 screen for predominantly 2.35:1 viewing while maximizing the capabilities of the projector's chip-resolution and light-output. That requires more than just a 2.35:1 screen, it requires as a vertical stretch capable scaler, anamorphic lens, lens transport, transport plate, etc.</p>

<p>Images on aspect ratios that are less wide (16:9, 4:3, for example) would have to be displayed without the anamorphic lens in front of the projector lens, and with side pillars on the wider 2.35:1 screen, but aligned with the top/bottom edges of the screen (reason by which it is called "constant height"). That is, if the original aspect ratio of the incoming image needs not to be altered. Additionally, pillar side-bar masking might be necessary for some viewers that can not tolerate "projected" black pillar bars, which are not as black as good masking material.</p>

<p>The approach could mean that the overall area occupied by a smaller 16:9 image within the 2.35:1 screen could end up unacceptably small to some viewers, compared to a 16:9 screen installation for predominantly 16:9 TV viewing occupying the same width of the 2.35:1 screen approach. Not to mention the further reduced visual impact of 4:3 images that would look even smaller when implemented in constant height 2.35:1 screen.</p>

<p><br />
<B>Limited Wall Width</B></p>

<p>With that in mind, whether the screen is mounted on a wall or coming from the ceiling, if the front viewing area is limited in width, a 2.35:1 screen could be seen as a step backwards regarding overall image size impact for viewers that seldom watch 2.35:1 content. Therefore, one has to decide very carefully what is the primary purpose of the home theater. If the purpose is to mainly watch TV in 4:3 or 16:9 formats and occasionally view a 2.35:1 movie, then the CinemaScope&trade; concept with 2.35:1 screens might not be as appealing as it would be to a 2.35:1 wide-screen movie fan that would not watch TV or smaller aspect ratios as often, or at all, on the home theater.</p>

<p><br />
<img src="/images/articles/hdht-screen.jpg" alt="HDHT Screen" align="right" /><B>Butchering Aspect Ratio</B></p>

<p>Many movie directors do not take lightly when anyone geometrically alters the original aspect ratio (OAR) of their piece of art to fit the image into a display device with different aspect ratio. Aspect ratio is part of the artistic creation and should be respected as is. Some HD movie channels alter (butcher) the original aspect ratio of wide-screen movies to make them fit within the 16:9 frame so they are displayed without letterbox bars. The content distributor thinks that people do not like to see bars, and actually many consumers purchased 16:9 screens under the impression that they would finally get rid of the hatred black bars on their 4:3 sets when viewing 16:9 content. The content distributors were not to far off in that thinking process "for the general mass of TV viewers", but they ignored the OAR loving public.</p>

<p>2.35:1 wide-screen movie content displayed on a 16:9 screen would display with a similar letterbox bar effect of the 16:9 content on the old 4:3 TV set. The approach wastes about 30% of the vertical resolution of the display device used for projecting just black dots, and the projected dots are generally not absolute black when they hit the screen. So how could we use that wasted vertical resolution capability for an image that rather needs it horizontally because is proportionally wider?</p>

<p><br />
<B>Putting All the Resolution Pixels to Good Use</B></p>

<p>A CinemaScope&trade; system could put those vertical pixels to work electronically with a capable scaler, it would stretch the image vertically, everyone would look thin, it would be ideal that someone later in the system path makes them normal again. This is where the anamorphic lens fits into place in the system. When placed in front of the projector lens it produces a similar stretch but now in the horizontal direction, and with optics. Both steps are needed, making first the image taller electronically with a scaler and later wider optically with the anamorphic lens. The combined effect of both actions produce an image that is larger but proportional to the original image when it was sandwiched between the black bars, maintaining the exact same aspect ratio as the original 2.35:1 source.</p>

<p><br />
<B>How Could We Use the System?</B></p>

<p>Many HD movie channels and even broadcast HD movies distribute 2.35:1 movies as they are, with top/bottom bars sandwiched within the 16:9 aspect ratio of the HD signal to maintain the original geometry of the content. Such wide-screen content could certainly display well in a CinemaScope&trade; capable home theater, the scaler and the anamorphic lens would do their job with that content as well, in other words, this is not about wide-screen [HiDef]DVDs pre-recorded movies only.</p>

<p><br />
<B>Does Resolution Matter in CinemaScope?</B></p>

<p>Having a 1080p projector rather than a lower resolution projector always helps on larger screens, but a 720p projector could also be used to implement CinemaScope&trade;. However, a 720p image barely has 1 million pixels of spatial resolution (720x1280= 921,600 pixels) displayed on each frame of content, while a 1080p projector more than doubles that (1080x1920=2,073,600 pixels). Regardless of the frame rate and the original resolution of the content, the above will be the spatial resolution outputted by the projector's chip.</p>

<p>If the primary purpose of a HT is to show CinemaScope&trade; movies, the spatial resolution factor is more important than the classical benefits of the faster temporal resolution of the 720p format, 60fps frame rate are well suited to fast sports, such as ESPN HD, but the horizontal line has only 1280 pixels, rather than 1920.</p>

<p>A non-1080p resolution projector could still do a decent job as long as the increased width of the horizontally larger screen (for an image that is horizontally expanded by the anamorphic lens) is not pushed beyond reasonable limits. Going too large might negatively affect the image quality for the particular viewing distance.</p>

<p>There are many more areas of discussion in the resolution subject, such as the ability to handle 24fps 1080p content originating from film stored in HD DVD and Blu-ray. There are projectors that are capable to accept 1080p at 24fps and can display it at multiples of that frame rate, without doing 2:3 pull-down to go to the 60i interlaced world to later de-interlace it to 60p. One should always avoid any unnecessary video processing and conversions to let the scaler to do its CinemaScope&trade; vertical stretch with a signal as clean as possible to avoid compound artifacts.</p>

<p><br />
<B>2.35:1 Screens, How People Use Them </B></p>

<p>1080p projectors are now available in good variety, as well as good quality anamorphic lenses/transports; scalers with vertical stretch ability for constant height installation are also available, all relatively affordable. 2.35:1 movie fans might see an opportunity to jump out of the 16:9 HD aspect ratio bandwagon and into the 2.35:1 CinemaScope&trade; style, to watch their movies on a screen with the same aspect ratio as the movie.</p>

<p>However, installing a 2.35:1 screen is only part of the solution, as we discussed earlier, a scaler has to stretch the image vertically to get rid of the top/bottom black bars and let the projector's chip use its full vertical resolution (1080 or 720), which means more projected light from 30% more pixels, then the anamorphic lens stretches the image horizontally.</p>

<p>Not doing the full combination of the steps above, and performing only manual adjustments like zooming, refocusing, masking, to push the top/bottom letterbox bars out of the 2.35:1 screen frame, even if they are covered with masking material, potentially looses precious projector chip resolution and light output. That approach is used by many home theater enthusiasts and is not within the scope of these articles. There is a cost/benefit on each solution, each person has to decide what is the best solution for their particular installation, quality, flexibility, easy of operation, and pocket. In theory they both need a capable projector and a 2.35:1 screen to start with, those two pieces could be reused when moving from one approach to the other.</p>

<p><br />
<img src="/images/articles/hdht-construction.jpg" alt="Construction" align="left" /><B>The Perfect Storm Team</B></p>

<p>I would like to thank all the companies that participated on this CinemaScope&trade; project for their collaboration and personal efforts:</p>

<p>Wing Chung, Director of Project Engineering, Optoma (DLP projector/scaler)<br />
Shawn Kelly, President Panamorph (anamorphic lens, mounting plate)<br />
Bob Yellin, President Z-bott LLC (anamorphic lens transport)<br />
Don Newquist, Principal HTIQ (masking/curtains HT system)<br />
Mark Haflich, Owner Soundworks (projector, screen, etc. dealer/installer)<br />
Chuck Williams, ISF calibrator<br />
Jose Granados, Owner Granados Co., (installation masking/curtains rail system)</p>

<p>When we started a couple of months ago, some of the products were still in a prototype stage or just released, and the installation and calibration efforts required creativity, breaking new ground to make things work. But we all capitalized from this learning experience in one way or another.</p>

<p>Regarding the installation in particular, I would like to thank Mark Haflich, owner of Soundworks in Kensington MD, for his valuable support and the professional dedication of his installation crew, who worked several days until very late and with whom I had the pleasure to share many inventive moments, as well as the nightmares, and the satisfaction of seeing some pieces fit under the powers of creativity, often without the benefit of instruction manuals or suitable installation hardware.</p>

<p>Shawn (Panamorph) and Wing (Optoma) have both been excellent team experts that facilitated their insider knowledge for the optimal harmonization of products, even those that their companies did not have in production yet.</p>

<p>I am still working with the final HT phases with Don (htiq, home theater electric masking/curtains, etc), regarding the pillar masking and motorized curtains for 4:3 and 16:9 viewing in the constant height system. He has been an excellent collaborator in making my curtain/masking system design possible. His expert advice in that area of the project has been very valuable.</p>

<p>It is important to note that I am not doing curtains and masking just to beautify the HT installation, but to primarily improve the perception of the image by the eyes. Image quality was and still is one of the main drivers of this project.</p>

<p><br />
<B>What is Next</B></p>

<p>In the next installments I will cover each of the areas about the projector, lens, transport, plate, procurement, installation, calibration, masking system, etc. CinemaScope&trade; at the consumer's home is warming up very quickly in 2007. It is possible, affordable, and breathtaking, and we could do a great service to our readers by providing them with a real case scenario to successfully repeat at their home.</p>

<p>Stay tuned for Part 3</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>March  6, 2007 11:52 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 557
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
 				AND entry_id <> 557
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/cinemascope_hdht_-_part_2.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
