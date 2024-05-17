<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1652";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1652 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="vice president, nvidia physx, nvidia corporation, visual computing, solution home, nvidia, NVIDIA, Vision, vision, games, stereoscopic, geforce, GeForce, game, gaming, new, president, glasses, high, stereo, home, world, vice, solution, technology" />
	<meta name="description" content="NVIDIA Corporation, in conjunction with the world's leading content developers, display manufacturers, and PC OEMs and system builders, is pleased to announce NVIDIA(R) 3D Vision(TM) for GeForce(R), the world's first high-definition 3D stereo solution for the home.

Forming the foundation for a new consumer 3D stereo ecosystem for gaming and home entertainment PCs, 3D Vision is a combination of high-tech wireless glasses, a high-power IR emitter and advanced software that automatically transforms..." />
	<title>HDTV Magazine Bulletins - NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
//		var federated_media_section = 'holiday';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');

		$base_url = strleftback(PHP_SELF, '/') . '/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('NVIDIA Announces 3D Vision-The World\'s First High-Definition 3D Stereo Solution for the Home'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2009/01/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>January  8, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=3D HDTV">3D HDTV</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2009/01/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2009/01/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2009/01/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home.php&amp;phase=2&amp;title=NVIDIA%20Announces%203D%20Vision-The%20World%27s%20First%20High-Definition%203D%20Stereo%20Solution%20for%20the%20Home&amp;bodytext=NVIDIA%20Corporation%2C%20in%20conjunction%20with%20the%20world%27s%20leading%20content%20developers%2C%20display%20manufacturers%2C%20and%20PC%20OEMs%20and%20system%20builders%2C%20is%20pleased%20to%20announce%20NVIDIA%28R%29%203D%20Vision%28TM%29%20for%20GeForce%28R%29%2C%20the%20world%27s%20first%20high-definition%203D%20stereo%20solution%20for%20the%20home.%0A%0AForming%20the%20foundation%20for%20a%20new%20consumer%203D%20stereo%20ecosystem%20for%20gaming%20and%20home%20entertainment%20PCs%2C%203D%20Vision%20is%20a%20combination%20of%20high-tech%20wireless%20glasses%2C%20a%20high-power%20IR%20emitter%20and%20advanced%20software%20that%20automatically%20transforms...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="prtitle">NVIDIA Announces 3D Vision-The World's First High-Definition 3D Stereo Solution for the Home</p>

<center><i>NVIDIA 3D Vision for GeForce Brings New Dimension to Photos, Videos and Games</i></center><br />
<br />

<p><B>LAS VEGAS, Jan. 8 /PRNewswire-FirstCall/ -- CONSUMER ELECTRONICS SHOW (CES) 2009</B> -- NVIDIA Corporation, in conjunction with the world's leading content developers, display manufacturers, and PC OEMs and system builders, is pleased to announce NVIDIA(R) 3D Vision(TM) for GeForce(R), the world's first high-definition 3D stereo solution for the home.</p>

<p>Forming the foundation for a new consumer 3D stereo ecosystem for gaming and home entertainment PCs, 3D Vision is a combination of high-tech wireless glasses, a high-power IR emitter and advanced software that automatically transforms hundreds of PC games into full stereoscopic 3D experiences. Designed to work with the new pure Samsung(R) and ViewSonic(R) 120 Hz LCD monitors, Mitsubishi(R) DLP(R) HDTVs, and the DepthQ HD 3D Projector by Lightspeed Design, Inc, 3D Vision unlocks crystal-clear, flicker-free 3D stereo imagery perfect for driving new experiences in 3D gaming, 3D movies, and 3D photography.</p>

<p>"Along with gaming innovations in Microsoft Windows and DirectX, NVIDIA 3D Vision proves there's never been a better time to be a PC gamer," said Corey Rosemond, group marketing manager, Windows Gaming. "By including support for previously released and upcoming Games for Windows and Games for Windows -- LIVE titles, PC gamers can expect a new level of immersion in full stereoscopic 3D, and enjoy broad support for the hottest games."</p>

<p>Powered by NVIDIA GeForce GPUs, the number one choice of gamers worldwide, 3D Vision is the world's highest quality stereoscopic 3D consumer solution, consisting of:</p>

<p>-- High-Tech, Wireless Active Shutter Glasses</p>

<p>-- Designed with top-of-the-line optics to deliver 2X the resolution per eye and ultra-wide viewing angles versus passive glasses. Comfortable to wear and modeled after modern sunglasses, offering a stylish and lightweight alternative to traditional 3D glasses. Fully untethered solution, offering free range of motion and up to 20 feet of wireless 3D viewing.</p>

<p>-- USB-based, High Power IR Emitter</p>

<p>-- Transmits data directly to active shutter glasses within a 20 foot radius and contains an easy to use real-time 3D adjustment dial.</p>

<p>-- Maximum Display Flexibility</p>

<p>-- Designed for pure ViewSonic and Samsung 120 Hz LCD monitors, Mitsubishi DLP 1080p HDTVs, and DepthQ HD 3D projectors, unlocking crystal- clear, flicker-free stereoscopic 3D gaming for multiple viewing solutions.</p>

<p>-- Out of the Box Game Compatibility</p>

<p>-- Advanced NVIDIA software automatically converts over 300 games to work in 3D stereo out of the box, without the need for special game patches. In addition, NVIDIA's "The Way It's Meant to Be Played" program ensures that future games will support 3D Vision. 3D Vision is also the only stereoscopic 3D gaming solution to fully support NVIDIA SLI(R), NVIDIA PhysX(TM), and Microsoft(R) DirectX(R) 10 technologies.</p>

<p>-- Extended Usability On a Single Charge</p>

<p>-- A single charge using a standard USB cable enables over 40 hours of continuous 3D stereoscopic gaming. Intelligent circuit design built into the glasses automatically shuts the glasses off after 10 minutes of inactivity to preserve battery life.</p>

<p>-- Support for 3D Stereo Photography and Movies</p>

<p>-- Includes a free 3D Vision viewer which allows consumers to take in-game screenshots and view them in 3D stereo, or import and view stereoscopic pictures and movies from a variety of different capture sources and online web photo galleries.</p>

<p>"For gamers, 3D Vision for GeForce represents a whole different way of experiencing the game, and for developers, it unlocks the potential of making the game literally pop off the screen," said Ujesh Desai, vice president of GeForce desktop business at NVIDIA. "From games to movies to photography, 3D Vision delivers a truly immersive awesome 3D experience."</p>

<p>3D Vision for GeForce is available starting today from leading U.S. e-tailers including www.compusa.com, www.tigerdirect.com, www.microcenter.com; as well as direct from www.nvidia.com for a suggested MSRP of $199 USD. Worldwide availability will be announced later, in the first quarter.</p>

<p><br />
<B>About NVIDIA</B></p>

<p>NVIDIA (NASDAQ:NVDA) is the world leader in visual computing technologies and the inventor of the GPU, a high-performance processor which generates breathtaking, interactive graphics on workstations, personal computers, game consoles, and mobile devices. NVIDIA serves the entertainment and consumer market with its GeForce(R) products, the professional design and visualization market with its Quadro(R) products, and the high-performance computing market with its Tesla(TM) products. NVIDIA is headquartered in Santa Clara, Calif. and has offices throughout Asia, Europe, and the Americas. For more information, visit www.nvidia.com .</p>

<p>Certain statements in this press release including, but not limited to, statements as to: the benefits, features, impact, and capabilities of NVIDIA 3D Vision, NVIDIA GeForce GPUs, and NVIDIA PhysX technology; and the impact of stereoscopic on video games; are forward-looking statements that are subject to risks and uncertainties that could cause results to be materially different than expectations. Important factors that could cause actual results to differ materially include: development of more efficient or faster technology; adoption of the CPU for parallel processing; design, manufacturing or software defects; the impact of technological development and competition; changes in consumer preferences and demands; customer adoption of different standards or our competitor's products; changes in industry standards and interfaces; unexpected loss of performance of our products or technologies when integrated into systems as well as other factors detailed from time to time in the reports NVIDIA files with the Securities and Exchange Commission including its Form 10-Q for the fiscal period ended October 26, 2008. Copies of reports filed with the SEC are posted on our website and are available from NVIDIA without charge. These forward-looking statements are not guarantees of future performance and speak only as of the date hereof, and, except as required by law, NVIDIA disclaims any obligation to update these forward-looking statements to reflect future events or circumstances.</p>

<p>Copyright (C) 2008 NVIDIA Corporation. All rights reserved. NVIDIA, PhysX, GeForce, Quadro, Tesla, and CUDA, are registered trademarks and/or trademarks of NVIDIA Corporation in the United States and other countries. All other company and/or product names may be trade names, trademarks and/or registered trademarks of the respective owners with which they are associated. Features, pricing, availability, and specifications are subject to change without notice.</p>

<p>What the Industry is Saying:</p>

<p>"NVIDIA 3D Vision has the potential to take PC gaming to a whole new level by bringing an unprecedented level of immersion to titles," said Christian Svensson, Corporate Officer/Vice-President - Strategic Planning & Business Development at Capcom. "When playing Dark Void with NVIDIA 3D Vision glasses, it really feels like you're inside the game as never before."</p>

<p>"Trying to describe how cool NVIDIA's 3D Vision is with words is like trying to draw Angelina Jolie on an Etch-A-Sketch. This is something you need to see with your own eyes to believe. 3D Vision literally takes gaming into the third dimension - something a lot of companies have aspired to over the years with little success," said Kelt Reeves, president of Falcon Northwest. "It took NVIDIA's full grasp of the gaming ecosystem, from developers to hardware and software solutions to produce the best consumer 3D experience I've ever seen. You've got to try this!"</p>

<p>"NVIDIA GeForce graphics processor technology is fundamentally changing the way our customers use their computers. By allowing real-time conversion of existing PC games into stereoscopic 3D and opening up new markets for 3D movies and pictures, 3D Vision will revolutionize how users interact with visual computing applications and is forming the foundation for a new consumer stereoscopic 3D ecosystem" said Chris Ward, president, of Lightspeed Design, Inc. "The DepthQ 3D HD Projector is the perfect application for a home theater room, allowing GeForce owners the ability to view games in stereoscopic 3D on Da-Lite projection screens up to nine feet wide."</p>

<p>"We're ecstatic to be able to bring the new NVIDIA 3D Vision technology to our customers," said Wallace Santos, president of Maingear Computers. "3D Vision really brings a whole new perspective to gamers, and is something that we will be recommending to anyone purchasing a MAINGEAR system equipped with NVIDIA graphics. With 3D Vision, NVIDIA continues to push the technology boundaries of what other companies can only dream about achieving."</p>

<p>"3D in the home is now an exciting reality, and Mitsubishi is proud to be partnered with NVIDIA to bring this solution to consumers," said Frank DeMartin, vice president, marketing, Mitsubishi Digital Electronics America. "By combining the Mitsubishi 60", 65" and 73" large screen home theater televisions, with more color than typical flat panel HDTVs, and the amazing visual computing power of GeForce graphics processors, consumers now have the premier platform for immersive home entertainment when they add 3D Vision to their computer."</p>

<p>"We believe 3D Vision will be a milestone for PC games and massively multiplayer online games in particular and enable players to enter a much more immersive game world", said Jerry Mao, vice president of object software, developer of Metal Knight Zero. "Compared with other MMOGs, Metal Knight Zero will benefit more from 3D Vision and provide players with a realistic game environment especially combined with NVIDIA PhysX technology which is already integrated into the game."</p>

<p>"We are excited to introduce the world's first pure 3D Vision-Ready 120 Hz desktop LCD 22" monitor for sale, delivering amazing 3D performance, picture quality, and response time" said R.A. Atanus, vice president of product marketing at Samsung Electronics. "The Samsung SyncMaster 2233RZ is the ultimate 3D widescreen gaming LCD monitor, and when paired with 3D Vision, gamers can instantly play hundreds of PC games into stereoscopic 3D."</p>

<p>"We are impressed by NVIDIA 3D Vision's amazing effects, which recreates visual effects like 3D theaters," said Chenglong Xu, vice technical director of TENCENT. "NVIDIA has enabled us to have this feature in our games, and we believe that players can enjoy a more realistic and innovative 3D experience powered by NVIDIA 3D Vision."</p>

<p>"There is no doubt that stereoscopic 3D is a very interesting step in the realm of 3D technology and it really does offer some impressive enhancements to PC games," said Ubisoft EMEA's Peter Hammer. "From the moment you put on NVIDIA 3D Vision glasses the visuals come to life!"</p>

<p>"NVIDIA GPUs and GeForce 3D Vision are helping drive new standards in dimensionalized viewing capabilities in desktop displays," said Jeff Volpe, vice president and general manager, ViewSonic North America. "NVIDIA 3D Vision and the ViewSonic FuHzion(TM) VX2265wm display will provide game enthusiasts with realistic depth, intense motion, rich graphics and detailed images that literally leap off the screen."<br />
Photo: NewsCom: http://www.newscom.com/cgi-bin/prnh/20090108/CLTH926<br />
http://www.newscom.com/cgi-bin/prnh/20020613/NVDALOGO<br />
AP Archive: http://photoarchive.ap.org/<br />
AP PhotoExpress Network: PRN13<br />
PRN Photo Desk, photodesk@prnewswire.com</p>

<p>Source: NVIDIA Corporation </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>January  8, 2009 06:45 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1652
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
 			<h2>More on 3D HDTV</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = '3D HDTV'
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

 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 1652
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Shane Sturgeon'
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
 				<h2>About Shane Sturgeon</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Bulletins</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>

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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2009/01/nvidia_announces_3d_vision-the_worlds_first_high-definition_3d_stereo_solution_for_the_home.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
