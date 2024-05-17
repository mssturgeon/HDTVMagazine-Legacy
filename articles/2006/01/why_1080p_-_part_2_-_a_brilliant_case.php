<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 297";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 297 AND placement_is_primary = 1";
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
	<meta name="keywords" content="motion adaptive, video processing, pixel pixel, pixel motion, adaptive deinterlacing, brillian, Brillian, set, video, pixel, fps, fields, sources, even, frames, image, deinterlacing, motion, odd, inputs, generation, quality, lines, adaptive, material" />
	<meta name="description" content="Following with the subject of 1080p, this is the second part of the series of articles about the technology.  Today we will look behind the curtain of how Brillian had implemented their 1080p magic into their recently released LCoS rear projection set." />
	<title>HDTV Magazine Articles - Why 1080p? - Part 2 - A Brillian(t) Case</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/why_1080p_-_part_2_-_a_brilliant_case';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Why 1080p? - Part 2 - A Brillian(t) Case'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Why 1080p? - Part 2 - A Brillian(t) Case</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>January 23, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php&amp;phase=2&amp;title=Why%201080p%3F%20-%20Part%202%20-%20A%20Brillian%28t%29%20Case&amp;bodytext=Following%20with%20the%20subject%20of%201080p%2C%20this%20is%20the%20second%20part%20of%20the%20series%20of%20articles%20about%20the%20technology.%20%20Today%20we%20will%20look%20behind%20the%20curtain%20of%20how%20Brillian%20had%20implemented%20their%201080p%20magic%20into%20their%20recently%20released%20LCoS%20rear%20projection%20set.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>Following with the subject of 1080p, this is the second part of the series of articles about the technology.  Today we will look behind the curtain of how Brillian had implemented their 1080p magic into their recently released LCoS rear projection set.</p>

<p>The company recently introduced their 65" 6580iFB 1080p LCoS set, which was slated to become available in 4Q05 and was the only size Brillian was planning to carry in 2005.  Brillian indicated that the video processing was implemented to get to the viewer all the resolution the 1920x1080 chip can promise, even with non-1080p sources.</p>

<p>During July/August of 2005, we held several technical exchanges with Vincent Sollitto (President and CEO), Hope Frank (Vice President of Marketing), and their technical team, continued with some meetings at the HDTV Display Search Conference held in Beverly Hills in late August, and culminated in January 2006 with a visit to their suite at CES to discuss with their engineers.   </p>

<p>Although I have seen the RPTV myself in several opportunities, the following material should not be misinterpreted as my endorsement of the product, or a technical confirmation of some of the statements provided by Brillian.  </p>

<p>The material might be more productive if the reader first becomes familiarized with the basic HDTV concepts of interlace and progressive I covered on other articles and the HDTV Glossary of this magazine; otherwise the information below could be a bit more technical than a casual reader might be comfortable with.  However, the subjects are covered with a tutorial approach, and are intended to help any reader to get acquainted with the concepts surrounding 1080p.   <br />
 <br />
<strong>Upconversion to 1080p</strong><br />
 <br />
This 1080p set displays images at 120 fps; in Brillian's opinion the image quality obtained at that frame rate is much better than just 60 fps, which is typically what most other 1080p sets do.  The video processor does not perform motion adaptation when jumping the frame rate from 60 to 120 fps; Brillian considers it unnecessary.</p>

<p><u>480i (NTSC) Inputs</u>:  Brillian uses pixel-by-pixel motion adaptive deinterlacers with 3:2 cadence detection and compensation combined with advanced low angle interpolation to produce a 720x480p image.  According to Brillian, this conversion process is as good as any in the industry today.  </p>

<p>Brillian then uses the highest quality scaling filters to upscale the image to 1440x1080, preserving the aspect ratio and converting from rectangular to square pixels.  If the user chooses one of the non-standard aspect ratios, the conversion will change to compensate.  For example, widescreen content viewed in the widescreen aspect ratio will be scaled horizontally to 1920, performing a one third stretch and converting from rectangular to square pixels.<br />
 <br />
<u>1080i Inputs</u>:  As many current 1080p HDTV manufacturers do, Brillian treats 1920x1080i video as 1920x540p frames.  According to Brillian, to differentiate its set from the competition and ensure the highest quality 1080p image is presented; Brillian uses a proprietary set of sophisticated scaling filters to vertically scale the 1920x540 fields to 1920x1080.  </p>

<p>As the next generation of image processors become more mature, the next generation 1080p units will incorporate hardware to perform the same high quality pixel-by-pixel motion adaptive deinterlacing on 1080i inputs Brillian currently only uses on 480i inputs.  Brillian stated: "Our next generation of products with pixel-by-pixel motion adaptive deinterlacing of 1080i sources will be brought to market when they are mature and don't cause more issues than they solve."</p>

<p><u>Progressive Inputs</u>:  Brillian accepts the standard 480p and 720p video formats as well as a multitude of PC formats such as VGA, SVGA, XGA, SXGA, and 1080p.  Brillian uses the highest quality scaling filters to convert these images to the 1920x1080 panels with options to preserve the aspect ratio or fill the screen.<br />
 <br />
A note on scaling filters:  Brillian does not use simple interpolation to scale the incoming data to fill its panels.  Interpolation, even the more advanced techniques, can cause loss of detail and in general have uncontrolled effects on the images.  Brillian uses up to 320 tap FIR filters to perform the image resolution conversion.  The use of FIR filters allow for control of the resulting image sharpness, which Brillian provides as its Picture Filter Modes.  Additionally, these scalers are multiregional, allowing for non-linear scaling to execute Brillian's Extended aspect ratios.<br />
 </p>

<p><strong>Deinterlacing Implementation</strong></p>

<p>Brillian does not add special artificial frames not intended by the material authors, however unless the image already comes externally as 60 fps, the set would have no other choice than to create 60 progressive frames from the provided 60 interlaced fields using pixel by pixel motion adaptive deinterlacing (480i).  </p>

<p>Further, if the original material was 24fps from film, then the 60 interlaced fields need to be converted to 60 progressive frames using inverse 3:2 pull-down.  Given such video processing, I questioned if the pixel-by-pixel motion adaptive deinterlacing is also used for the added frames, in addition to the motion adaptation used for joining the fields.</p>

<p> 	They clarified that in their view 1080i deinterlacing is really no different than 480i deinterlacing and follows the same rules or patterns.  Standard video sources (those recorded interlaced) are handled by combining each field with the previous taking into account motion to prevent combing or blurring effects.  </p>

<p>If the 60Hz interlaced source has the following fields A, B, C, D, E, then the process produces progressive frames 1-4 which are 1 (a combination of fields A and B), 2 (a combination of fields B and C), 3 (a combination of fields C and D), 4 (a combination of fields D and E) and so on.  </p>

<p>In some sense, blending these fields together does produce images unique from the original material but motion adaptive deinterlacing should further reduce the artifacts generated by the process.  By how much and if it will be noticeable at all will highly depend on the content.  The result is something close to what would be viewed on a phosphor based monitor where only the lines contained in each of the fields are actively driven and decay while the other lines are driven on the next field.<br />
 <br />
Film sources at 24Hz have progressive frames A, B, C, D.  These sources are converted to 60Hz interlaced formats (like 480i and 1080i) by showing half the lines (odd) of A, then the other half of the lines (even) of A, then the first half of the lines (odd) again of A, then half the lines (even) of B are shown, followed by the other half of the lines (odd) of B, etc.  So the 60Hz fields sequence is A odd, A even, A odd, B even, B odd, C even, C odd, C even, D odd, D even.  </p>

<p>According to Brillian, the proper way to deinterlace this content is to merge the even and odd lines of A to form one progressive scan frame and show it once for each original interlaced field or 3 times for A, C and correspondingly 2 times for B, D.  The de-interlaced 60Hz outcome results in the original film frames being shown A, A, A, B, B, C, C, C, D, D.  </p>

<p>Therefore, 60Hz is always derived without adding unique frames.  Certain frames are repeated for film sources, but they are not altered just repeated.  This ensures that the Brillian image quality remains as the author intended, versus trying to combine the fields from two separate frames of film material, which would create unintended blurry images.</p>

<p>The 1080p set does not do 3:3 video processing to display 72 frames from 24fps sources, but rather upconverts the 24 to 60 fps (Pioneer Elite plasmas are known to have the 72fps capability, more suitable for displaying film based content)</p>

<p><br />
<strong>1080p Acceptance</strong></p>

<p>Brillian reassured that their 1080p set is capable to accept an external 1080p signal on its digital (DVI) input, as 24, 30, or 60 fps.  The set's hardware can support 1920x1080p 24Hz and 30Hz ATSC standards.  This includes the transmission of the video data to the display section without altering the resolution of the 1920x1080p image.  </p>

<p>An accepted 60fps 1080p signal is passed to the display as is without video processing, however, 24fps and 30fps DVI inputs are currently frame rate converted to 60fps using a video buffer with some loss of temporal/spatial resolution pixels due to video processing (about 30%).  Future software upgrades may overcome this performance degradation.  As these sources become readily available, Brillian's software can be upgraded to take full advantage of this hardware path (more on it further down).</p>

<p>The TV's hardware can support 1920x1080p at 24Hz and 30Hz on the VGA and High Definition Component inputs.  However, 60Hz 1920x1080p analog sources will be too fast for the system.  The A/D converter itself is only 140MHz, so the VGA 148.5MHz standard will not run cleanly.  All the circuitry past the A/D converter is fast enough for 1080p 60Hz at 148.5MHz, up to and including the display's pixel matrix.</p>

<p>If the source of the material supports the CEA standard timings for 1080p at 24Hz or 30Hz, the set will be able to display this format.  However, since analog sources are not data enabled like DVI/HDMI, the source needs to provide the correct timing formats or else the data will not be detected and displayed properly.  </p>

<p>It is important to note that although I am very specific on quoting some limitations on the way this set accepts 1080p (because readers looking for that feature deserve honest detail), the fact that the set actually accepts 1080p is putting this set in a very unique class of only a couple of first generation RPTV sets available today.  Brillian has made the effort to provide 1080p inputs on this first generation and that has an important future-proof value that most other manufacturers could not match on their recently released 1080p sets, although some have already announced their plans to provide such feature in the near future. </p>

<p><br />
<strong>Upgradeability</strong></p>

<p>As these 24Hz and 30Hz 1080p sources become more prevalent the Brillian software may need to be updated to support all the nuances of the video timing, but the hardware platform is in place.</p>

<p>Brillian's current thinking is that there are so few devices providing material at this resolution and rate today that it is difficult to predict if they become more common and if the external sources will continue to conform to the standards.  Given this, Brillian said that software updates are available.  </p>

<p>When inquiring about Brillian's plans of software upgradeability for TVs that were purchased with the current software, and how they could investment-protect consumers who buy the first generation 1080p model, the response was: "Brillian provides the new firmware on its website for home service technicians and home installers to access and install for customers who require the upgraded features.  The User's Manuals are also available to support the new firmware on the same web site."</p>

<p>Brillian is working on the next generation video processing for 1080i deinterlacing to 1080p; the company indicated that they have no details as to how future hardware/software solutions for this feature would be implemented in current models, "if" it can be implemented as an upgrade.  <br />
 	</p>

<p><strong>Integrated Tuners, FireWire, ISF, etc.</strong></p>

<p>Although the following items are not necessarily related to the 1080p subject, consumers interested on this 1080p set might want to know how certain features are implemented. </p>

<p>Regarding tuning and connectivity capabilities, Brillian's 1080p set was suited with simple ATSC and Cable QAM on-the-clear tuners to meet basic tuning capabilities.  The CableCARD option was not pursued after an initial effort when finding out of the need to redo both tuner and Card to suit them for bidirectional capabilities, when implemented at a later time.<br />
 <br />
The 1080p set does not have 1394 connections even though the hardware can support it from a design standpoint.  Brillian considered that the integrated basic tuners are not usually what customers of this type of TV use for HD reception, they typically use a Cable or OTA STB, which should have 1394 outputs to facilitate HD external recording (on D-VHS for example), in addition to possibly have integrated HD-DVR capabilities for time-shifting purposes.  The inclusion of 1394 interfaces on the second-generation sets will depend on market demand.<br />
 <br />
Brillian also showed at their CES suite a demo of a technology demonstration of a prototype 65" 1080p set that was actually a monitor configuration with a variety of external video processors showing how each performed 1080i to p deinterlacing.  This concept will offer videophiles the ability to have a true video system of components as audio does today.  Brillian also provided some insight into the performance achievable in future models, they also declared to be happy with the performance of the Silicon Optix chip.</p>

<p>The model that is in production has the ability to perform a wide variety of ISF calibration functions from the user menu (which could also be locked out to avoid accidental changes); there is no need to go to the service menu for the access to that functionality (as with other manufacturers, if they do provide access at all).  Some adjustments include selection of color palette (e.g. PC levels at 0-255 gray shades and TV levels at 16-235), 3 color-temperatures (normal 8500 Kelvin, cool 13000, warm 6500) that are also adjustable, sharpness filters, picture modes for each input, etc.  </p>

<p>All typical menu settings such as contrast, brightness, etc., are set at halfway levels out of the box, as opposed to what many competitors do, usually cranking up the contrast and other settings to impress favorably on fluorescent lighted retail floors; many uninformed consumers continue using those settings at home, not obtaining the best image the set could provide at the home environment.<br />
 <br />
It also features a 200-page user manual I have not seen yet but quoted of exceptional clarity.  Upon purchasing this TV, an ISF (Imaging Science Foundation) technician visit is also included to perform calibration service for two inputs, which typically could run in the range of $300-$500 if hired separately; such feature is certainly an innovation among the competition, and shows that Brillian strives to produce the best quality image the TV could offer to a consumer. </p>

<p><br />
<strong>Brillian Moving Forward</strong></p>

<p>According to Brillian, their sets distinguish themselves from other LCoS 1080p manufacturers in the way they employ an analog drive scheme with their pixel array, giving a much better result with less noise and contouring artifacts than the other digital implementations, such as JVC's DILA.  It's method of uniformity compensation is also unique and ensures even color rendering across the screen in solid images.<br />
  <br />
In the words of Brillian: "Pixelworks has been a good partner.  They have provided us a quality chip-set and base design kit.  Brillian's engineers have invested 2 years to customize the design to extract the distinguishing performance from the system."  Today they have a very capable system, which Brillian said is getting good reviews including Best HDTV of 2005 from several industry experts.  <br />
 <br />
Moving forward to next generation designs, Pixelworks, along with all of the major video processor chip designers offer, will offer new chip sets to support the all-important pixel-by-pixel motion adaptive deinterlacing of 1080i sources.  Brillian continues to evaluate these chip sets, as well as those from other companies, to insure best in-class performance is delivered.  <br />
 <br />
Silicon Optix is one such company under evaluation.  Their market buzz and pixel-by-pixel motion adaptive noise reduction makes Silicon Optix a player to be closely watched, Brillian said.  I have watched them and they have certainly progressed quite well judging by the manufacturers adopting their video processing technology since they introduced to the public their Realta chip at CES 2005, read the details at my HDTV Technology and CES 2005 report available at the pages of this HDTV Magazine.</p>

<p><br />
Make sure you read the next article of this 1080p series, coming soon.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>January 23, 2006 07:00 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 297
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
 				AND entry_id <> 297
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
