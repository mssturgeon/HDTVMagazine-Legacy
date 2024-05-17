<?
	require('/var/www/html/global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="http://www.movabletype.org/">
	<meta name="keywords" content="high definition, sanyo xacti, still images, memory card, digital media, video, sanyo, SANYO, definition, high, mode, digital, still, camera, recording, Xacti, xacti, images, media, new, card, features, memory, shooting, lcd">
	<meta name="description" content="A video recording mode optimized for the video iPod&amp;reg;, 16:9 widescreen still picture mode and easy, convenient in-camera video editing. The SANYO Xacti HD1a features an ergonomic, one-handed operation. It can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. in September 2006 at a very competitive $699.99** MSRP.">
	<title>HDTV Bulletins: New Sanyo High-Definition Digital Media Camera</title>

	<link rel="alternate" type="application/atom+xml" title="Atom" href="http://www.hdtvmagazine.com/news/atom.xml">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://www.hdtvmagazine.com/news/index.xml">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://www.hdtvmagazine.com/news/rsd.xml">

	<script type="text/javascript">
		function newsvine(url, headline) {
			var h = 480;
			var w = 590;
			var t = (screen.height - h) / 4;
			var l = (screen.width - w) / 4;

			window.open('http://www.newsvine.com/_wine/save?u='+url+'&h='+headline, 'Newsvine', 'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+t+',left='+l)
		}
	</script>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		
		$google_channel = $GOOGLE_CHANNEL['Shane Sturgeon'];
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/new_sanyo_high-definition_digital_media_camera';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode('New Sanyo High-Definition Digital Media Camera');
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php";
		
		// Do different things depending on which blog type is being published
		$show_more_from = false;
		switch (7) {
			case 1: # Articles
				$show_more_from = true;
				if ($_SESSION[subscriptions] & SUB_ARTICLES) {
				} else {
					$sub_text = '<b>Receive instant notification of new articles.</b> <a href="'. URL_PROFILE_CREATE .'">Register Now</a>'.
					' and/or <a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
				}
				break;
			case 6: # Test
				$show_more_from = true;
				break;
			case 7: # News Bulletins
				if ($_SESSION[subscriptions] & SUB_BULLETINS) {
				} else {
					$sub_text = '<b>Receive instant notification of HDTV Bulletins.</b> <a href="'. URL_PROFILE_CREATE .'">Register Now</a>'.
					' and/or <a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV News Bulletins via email as soon as they are published.';
				}
				break;
			default:
		}
	?>
	
		<div class="content">
			<table class="bare" cellpadding=0 cellspacing=0 style="width:100%">
				<tr>
					<td id="article_headshot">
						&nbsp;<!-- Placeholder for headshot -->
					</td><td>
						<?if ($sub_text != '') {
							echo '<div align="center"><div class="alertbox">'. $sub_text .'</div></div>';
						}?>
						<!-- Article Header -->
						<table class="bare" cellpadding=0 cellspacing=0 style="width:100%">
							<tr>
								<td class="article_title" colspan=2>New Sanyo High-Definition Digital Media Camera</td>
							</tr><tr>
								<td id="article_byline" nowrap>
									By <b>Shane Sturgeon</b> on <b>July 25, 2006</b>
								</td><td id="article_links">
									<span><img src="/images/digg.png" alt="Digg Article" align="absmiddle"><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php&amp;phase=2">Digg</a></span>
									<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle"><a href="javascript:newsvine('http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php', '<?=$title_encoded?>')">Newsvine</a></span>
									<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle"><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
									<span><img src="/images/save.gif" alt="Save Article" align="absmiddle"><a target="_blank" href="<?=$save_url?>">Save</a></span>
									<span><img src="/images/email.gif" alt="Email Article" align="absmiddle"><a href="<?=$email_url?>">Email</a></span>
									<span><img src="/images/print.png" alt="Print Article" align="absmiddle"><a target="_blank" href="<?=$print_url?>">Print</a></span>
								</td>
							</tr>
						</table>
							
					</td>
				</tr><tr>
					<td id="ad-left">
 						<?include(BASE_DIR .'/ads/skyscraper.php');?>
					</td><td style="vertical-align:top; padding-top:20px;">
						<span id="intelliTXT"><p class="editorial">This press release is actually over a week old, but due to the website difficulties we were experienceing at the time, it was never published. Hopefully it is still news to some of you, although it is not necessarily a "bulletin" anymore. &raquo; Shane</p>

<center><b>New SANYO High-Definition Digital Media Camera Features Optimized Recording for Portable Video Players and 16:9 Still Mode Ideal for Widescreen Viewing</b></center>

<p>SANYO Xacti HD1a Offers In-Camera Editing, 2.2-inch LCD Display and $699.99 MSRP</p>

<p>CHATSWORTH, Calif., July 13 /PRNewswire-FirstCall/ -- A video recording mode optimized for the video iPod&reg; and other portable video players tops the advanced feature set of the new SANYO Xacti HD1a high-definition compact digital media camera. Following quickly on the strong sales and critical success of the SANYO Xacti HD1 introduced in January 2006, the new high-definition HD1a also adds such compelling features as a selectable 16:9 widescreen still picture mode and easy, convenient in-camera video editing.</p>

<p><img src="/images/bulletins/Xacti-HD1a.jpg" alt="Xacti HD1a"></p>

<p>The SANYO Xacti HD1a retains its title as the world's smallest and lightest high-definition digital media camera*. Featuring ergonomic, one-handed operation, the camera can record both 720p high-definition video and 5.1 megapixel digital still images to a standard SD flash memory card. It will be available in the U.S. from SANYO, the world's leading manufacturer of digital cameras and components, in September 2006 at a very competitive $699.99** MSRP.</p>

<p>"The remarkable success of our precedent-setting Xacti HD1, along with user feedback, prompted SANYO to incorporate several new key features into the new HD1a," said John Lamb, Senior Marketing Manager, for SANYO Fisher Company's Audio Video Division. "Our inclusion of a 30 frames-per-second 320 x 240 pixel video recording mode -- optimized for viewing MPEG-4 video clips on a video iPod or other portable video player -- reflects a shift by consumers towards on-the-go viewing."</p>

<p><b>Video recording optimized for personal media players</b><br />
The HD1a's new "Web-SHQ" recording mode is designed specifically to capture video destined for the video iPod and other popular MPEG-4 capable personal media players. For optimal playback on such devices, video is captured at a resolution of 320 x 240 pixels and a smooth and natural 30 frames per second.</p>

<p><b>16:9 still shooting</b><br />
An all-new 16:9 shooting mode allows users to capture 3.8 megapixel stills in the same widescreen format as their high-definition videos for eye-catching viewing on a 16:9 television screen.</p>

<p><b>Convenient in-camera video editing</b><br />
The HD1a features enhanced video editing functions, enabling quick A>B deletions and easy combining of video clips. In-camera editing makes it easy to remove unwanted material and helps conserve memory card space.</p>

<p><b>High-definition engine</b><br />
The SANYO Xacti HD1a is powered by high-precision LSI (large-scale integration) circuitry for advanced, high-definition image processing. This powerful "high-definition engine" quickly executes a vast number of calculations and enables the HD1a to realize image processing functions such as high-definition 720p processing, real-time MPEG-4 compression and noise reduction.</p>

<p><b>2.2-inch LCD display</b><br />
The HD1a features a large Sanyo-developed 2.2-inch LCD display with 210,000 total pixels for exceptional viewability. The display flips out from the camera and rotates up to 285 degrees on axis for taking great video or still images even in difficult locations.</p>

<p><b>10x optical zoom</b><br />
The HD1a features a generous and highly efficient 10x optical zoom lens. Built from 12 elements designed in 9 groups and with a built-in neutral density filter, the 10x zoom lens has a maximum aperture of f/3.5 in both wide and telephoto angles, allowing for clear images in lower light situations. Combined with a 10x digital zoom capability, the HD1a is capable of a total 100x zoom.</p>

<p><b>Digital video and stills, all in one</b><br />
Like the Xacti HD1 and all previous SANYO Xacti digital media cameras, the HD1a can record still images in addition to video clips. Utilizing the 5.36 megapixel (total) CCD, the HD1a captures beautiful 5.1 megapixel still images which are recorded directly onto a standard SD memory card. The camera can record both 5.1 megapixel still images and high-definition (1280 x 720-pixel) digital video at the same time with a simple press of the shutter button during the shooting of a video clip.***</p>

<p><b>21 minutes of HD video per Gigabyte of memory</b><br />
The HD1a can record up to 21 minutes of 720p HD video per Gigabyte on a standard SD or SDHC memory card. That's up to 42 minutes on a 2-Gigabyte card or 84 minutes on the soon to be released 4-Gigabyte SDHC card (cards sold separately). Alternatively, HD1 users can select to record in Standard Definition mode (640 x 480 pixels at 30fps progressive) for up to one hour per Gigabyte of recording capacity. Users can quickly switch between high-definition and standard-definition recording modes by simply pressing the "HD/Norm" button located beneath the LCD display.</p>

<p><b>Ergonomic and lightweight</b><br />
Designed for convenient, one-handed operation and one-thumb control of most key functions, the truly ergonomic SANYO HD1a is ready to use whenever inspiration strikes, at home or away. Lightweight at only 8.3 ounces (including battery and a standard SD memory card), the HD1a measures 3.1" W x 4.7" H x 1.4" D.</p>

<p><b>Key SANYO Xacti HD1a features include:</b><br />
- 320 x 240 "Web-SHQ" video mode; optimal for playback on many personal media players<br />
- 16:9 widescreen MPEG-4 video (HD-SHQ / HD-HQ modes)<br />
- 16:9 widescreen digital still mode for stunning widescreen TV playback<br />
- Highly efficient 10x optical zoom<br />
- 2.2" rotating LCD display<br />
- CD-quality AAC-LC (MPEG-4 Audio) stereo recording<br />
- Enhanced video editing functions for quick A>B deletions and easy combining of clips<br />
- 60 fps Fluid Motion Recording (640 x 480 TV-HR Mode)<br />
- Rapid 5.1 megapixel sequential still shooting<br />
- Pop-up flash with double the brightness of conventional models<br />
- Anti-shake digital image stabilizer<br />
- Talking navigation guide for first-time users<br />
- Super-fast 1.7-second camera startup<br />
- Versatile manual mode enables advanced-control shooting<br />
- Super Macro shooting down to 1 cm (W) / 1 m (T)<br />
- Self timer (2 seconds / 10 seconds)<br />
- Voice recorder function: over 33 hours recording time with optional 2 GB SD Memory Card<br />
- Red-eye reduction mode<br />
- Multifunction docking station<br />
- High-capacity SANYO rechargeable Lithium-ion battery<br />
- Remote control included<br />
- Exif Print and Print Image Matching III<br />
- PictBridge-capable for PC-Free printing with PictBridge-compatible printers</p>

<p>SANYO Electric Co., Ltd. (Nasdaq: SANYY) is a $23 billion manufacturer and distributor of consumer and commercial electronics, including multimedia and telecommunication products. Based in Chatsworth, California, SANYO Fisher Company (a division of SANYO North America Corporation, a subsidiary of SANYO Electric Co., Ltd.) markets digital cameras, PCS phones, audio systems, portable and mobile electronics, televisions, DVD players, dictation devices, home appliances, LCD projectors, security video equipment and air conditioning systems.</p>

<p>For more information and additional specifications, please visit http://www.sanyodigital.com. Visit http://www.sanyodigital.com and click on "HD1a" > "Dealer Images" for downloadable hi-res product images.</p>

<p>All products and trademarks are the property of their respective owners. Because its products are subject to continual improvement, SANYO reserves the right to modify product design and specifications without notice and without incurring any obligations.</p>

<p>*   Among commercially available high-definition media cameras, as of July 13, 2006.<br />
**  Estimated Selling Price: Actual prices set by dealer are subject to change.<br />
*** Depending on the mode used to take still images, simultaneous video clip shooting may be interrupted.</p>

<p><u><b>Sanyo Press Release:</b></u><br />
<a href="http://www.sanyo.com/aboutsanyo/press_releases_detail.cfm?id=171">http://www.sanyo.com/aboutsanyo/press_releases_detail.cfm?id=171</a></p>

<p><u><b>Product Page:</b></u><br />
<a href="http://www.sanyo.com/entertainment/cameracorder/index.cfm?productID=1239">http://www.sanyo.com/entertainment/cameracorder/index.cfm?productID=1239</a></p>

<p><u><b>Editorial Contact:</b></u><br />
Michael R. Harris, Harris Public Relations<br />
for SANYO Fisher Company<br />
Tel: (714)966-0258, E-mail: hpr1@earthlink.net</p></span>
						<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>July 25, 2006 04:26 AM</b></p>

						<!-- Comments -->
						<h2>Reader Commentary</h2>
						<table class="type2" cellpadding=3 cellspacing=0>
							<tr><td><?
								$qry = "SELECT t.topic_id, topic_title, topic_replies FROM aux_mt_entry a, phpbb_topics t WHERE a.topic_id = t.topic_id AND entry_id = 402";
								$result = mQuery($qry);
								if (mysql_num_rows($result) > 0) {
									$row = mysql_fetch_assoc($result);
									$comment_url = URL_FORUM_VIEWTOPIC .'?t='. $row['topic_id'];
									echo 'See Forum Topic: <a href="'. $comment_url .'">'. $row['topic_title'] .'</a> <span style="color:#666666">('. $row['topic_replies'] .' replies)</span>';
								} else echo '&nbsp;';
							?></td></tr>
						</table>
							
						<!-- Related Articles by Category -->
						<h2>Related Articles</h2>
						<table class="type2" cellpadding=3 cellspacing=0>
							
								<tr>
									<td class="date">Jul 25,  4:07AM</td>
									<td><a href="http://www.hdtvmagazine.com/news/2006/07/new_sanyo_high-definition_digital_media_camera.php">New Sanyo High-Definition Digital Media Camera</a><span style="color:#666666"> (Shane Sturgeon)</span></td>
								</tr>
							
						</table>
							
						<?if ($show_more_from) {?>
							<!-- NOTE: Only show recent articles for the 'Articles' and 'Test' blogs -->
							<!-- Recent Articles by Author (exclude this one)-->
							<h2>More from Shane Sturgeon</h2>
							<table class="type2" cellpadding=3 cellspacing=0><?
								$qry = "SELECT entry_created_on, entry_title, entry_basename".
								" FROM mt_entry e, mt_author a".
								" WHERE".
								"	entry_blog_id = 7".
								"	AND entry_id <> 402".
								"	AND entry_status = 2".
								"	AND entry_author_id = a.author_id".
								"	AND a.author_name = 'Shane Sturgeon'".
								"	ORDER BY entry_created_on DESC".
								"	LIMIT 5";
								$result = mQuery($qry);
								if (mysql_num_rows($result) > 0) {
									while ($entry = mysql_fetch_assoc($result)) {
										$ts = strtotime($entry['entry_created_on']);
										$y = date('Y', $ts);
										$m = date('m', $ts);
										echo '<tr>'.
										'	<td class="date">'. date('M j, g:ia', $ts) .'</td>'.
										'	<td><a href="/news/'. $y .'/'. $m .'/'. $entry['entry_basename'] .'.php">'. $entry['entry_title'] .'</a></td>'.
										'</tr>';
									}
								} else echo '<tr><td>&nbsp;</td></tr>';
							?></table>
						<?}?>
							
						<!-- Author Biography -->
						<?
							$bio_result = mQuery("SELECT au.* FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
							$bio = mysql_fetch_assoc($bio_result);
						?>
						<h2>Who is Shane Sturgeon?</h2>
						<table class="type2" cellpadding=3 cellspacing=0><tr>
							<td><?=stripslashes($bio['bio'])?></td>
						</tr></table>
							
					</td>
				</tr>
			</table>

		</div>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
		
		// Insert IntelliTXT based on author preferences
//		if ($INTELLITXT_CHANNEL['Shane Sturgeon'] != '' && !access(ACCESS_PREMIUM)) {
		if ($INTELLITXT_CHANNEL['Shane Sturgeon'] != '') {
			echo "\n".'<script type="text/javascript" src="http://hdtvmagazine.us.intellitxt.com/intellitxt/front.asp?ipid='. $INTELLITXT_CHANNEL['Shane Sturgeon'] .'"></script>';
		}
	?>
</body>
</html>
