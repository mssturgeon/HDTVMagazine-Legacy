<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1526";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1526 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dtv transition, sub channels, transition help, help part, horizontal pixels, DTV, dtv, digital, channels, channel, quality, analog, image, content, HDTV, resolution, hdtv, pixels, help, same, MHz, broadcast, could, mhz, transition" />
	<meta name="description" content="Part 2 is dedicated to some technical aspects and benefits brought by the DTV implementation. 

DTV includes HDTV and SDTV, HDTV is a major improvement having 9 times the image quality of analog just in resolution terms, and SD is efficient enough to be able to broadcast 4-6 SD channels over the same bandwidth reserved for one HD channel (or one analog channel) in areas where that line up is needed. 

DTV also allows for the simultaneous broadcasting of both HD and SD, whereby SD uses part of the bandwidth required for HD on the same channel slot, which could be a good benefit, but could possibly harm the quality of the parallel HD program if overused. DTV also has..." />
	<title>HDTV Magazine Articles - DTV Transition - Can YOU Help? (Part 2) - A Technical View</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/dtv_transition_-_can_you_help_part_2_-_a_technical_view';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('DTV Transition - Can YOU Help? (Part 2) - A Technical View'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">DTV Transition - Can YOU Help? (Part 2) - A Technical View</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>October 23, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Digital (DTV) Transition">Digital (DTV) Transition</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php&amp;phase=2&amp;title=DTV%20Transition%20-%20Can%20YOU%20Help%3F%20%28Part%202%29%20-%20A%20Technical%20View&amp;bodytext=Part%202%20is%20dedicated%20to%20some%20technical%20aspects%20and%20benefits%20brought%20by%20the%20DTV%20implementation.%20%0A%0ADTV%20includes%20HDTV%20and%20SDTV%2C%20HDTV%20is%20a%20major%20improvement%20having%209%20times%20the%20image%20quality%20of%20analog%20just%20in%20resolution%20terms%2C%20and%20SD%20is%20efficient%20enough%20to%20be%20able%20to%20broadcast%204-6%20SD%20channels%20over%20the%20same%20bandwidth%20reserved%20for%20one%20HD%20channel%20%28or%20one%20analog%20channel%29%20in%20areas%20where%20that%20line%20up%20is%20needed.%20%0A%0ADTV%20also%20allows%20for%20the%20simultaneous%20broadcasting%20of%20both%20HD%20and%20SD%2C%20whereby%20SD%20uses%20part%20of%20the%20bandwidth%20required%20for%20HD%20on%20the%20same%20channel%20slot%2C%20which%20could%20be%20a%20good%20benefit%2C%20but%20could%20possibly%20harm%20the%20quality%20of%20the%20parallel%20HD%20program%20if%20overused.%20DTV%20also%20has...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the "DTV Transition - Can YOU Help?" series. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_1_-_transition_reception_and_help.php">DTV Transition - Can YOU Help? (Part 1) - Transition, Reception and Help</a></li>
<li><a href="/articles/2008/10/dtv_transition_-_can_you_help_part_3_-_tvs_vs_households.php">DTV Transition - Can YOU Help? (Part 3) - TVs vs. Households</a></li>
<li><a href="/articles/2008/11/dtv_transition_-_can_you_help_part_4_-_dtv_tuner_integration.php">DTV Transition - Can YOU Help? (Part 4) - DTV Tuner Integration</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_5_was_tuner_integration_timed_right.php">DTV Transition - Can YOU Help? (Part 5) - Was Tuner Integration Timed Right?</a></li>
<li><a href="/articles/2008/12/dtv_transition_-_can_you_help_part_6_-_subsidy_set-top-boxes.php">DTV Transition - Can YOU Help? (Part 6) - Subsidy Set-Top-Boxes</a></li>
</ul></div>
<br />
<p align="center"><b>Part 2 - A Technical View</b>  <p>Part 2 is dedicated to some technical aspects and benefits brought by the DTV implementation.  <p>DTV includes HDTV and SDTV, HDTV is a major improvement having 9 times the image quality of analog just in resolution terms, and SD is efficient enough to be able to broadcast 4-6 SD channels over the same bandwidth reserved for one HD channel (or one analog channel) in areas where that line up is needed.  <p>DTV also allows for the simultaneous broadcasting of both HD and SD, whereby SD uses part of the bandwidth required for HD on the same channel slot, which could be a good benefit, but could possibly harm the quality of the parallel HD program if overused. DTV has also the potential of <a href="http://en.wikipedia.org/wiki/Datacasting" target="_blank">datacasting</a>.  <p>When writing articles about DTV, many journalists focus on the politics, the government issues, the anxiety of possible failures, delays, weakness points, etc. I focus on working for the DTV system to be successful, for which I am asking your help in this series of articles.  <p>The technical and quality benefits of DTV are plenty, even if the DTV implementation is further delayed or needs another budget boost. After more than 20 years of effort, HDTV is here to stay and we have to make the DTV transition be successful.  <p>Sister technologies such as communications, music, photo, etc. have already migrated to digital. The technical world keeps moving in the direction of ones and zeros, and television is no exception.  <p>Sometimes the concept of "ones and zeros" facilitates the opportunity of over-compressing a digital signal that might be already penalized by the limited sampling of an infinite analog original of images we see and sounds we hear in real life, an analog world.  <p>DTV is no exception for that either. As with other digital-everything experiences, quantity models driven by moneymaking temptations might impact the quality of media, and you can help: demand quality be preserved!  <p>If you are not technically oriented, ignore the numbers in this article and scroll through the concepts to familiarize yourself with some benefits of DTV over analog TV. If you need more clarity and detail on some HDTV terminology please consult the <a href="http://www.hdtvmagazine.com/glossary.php" target="_blank">HDTV Glossary</a>.  <h2>Is DTV So Different?</h2> <p>You thought the current NTSC analog TV was fine, so why "fix" it?  <p>What about if DTV offers you an HDTV image that is 9+ times the resolution of your current analog NTSC TV?  <p>The NTSC image resolution is made of 480 viewable lines of 450 horizontal pixels each. HDTV is as high as 1080x1920. Do the math. Which one should look better?  <p>You thought DVD was even better than NTSC analog TV. What about an HD image that is 6 times the resolution of DVD?  <p>The DVD format is 480x720. HDTV is as high as 1080x1920. Do the math. Which one should look better?  <p>Is there anything better than broadcast 1080i HDTV? What about Blu-ray pre-recorded HD discs with 1080x1920 <b>progressive</b> at 24 frames per second for film based content. Play those on a large screen at home, and it would be hard to return to the local theater, unless you are missing the popcorn and soda on the floor.  <p>All of the above brought to you courtesy of the hard HDTV effort of the past 20+ years.  <p>How can someone disregard these technology advances? Perhaps by not being able to detect the differences in quality at the home environment. How could you appreciate those differences?  <p>Anyone should be able to appreciate the HD picture quality improvements on a larger TV screen viewed at the appropriate distance, but not if using the 13-inch TV in the kitchen viewed from 20 feet away. Even VHS might look the same to anyone under those conditions.  <p>That is probably the reason why many people with small screens, or viewing from too far away, might wonder: why H/DTV? I see no difference!  <p>Or why Blu-ray? DVD looks the same to me! Check out the <a href="http://www.hdtvmagazine.com/articles/2008/02/2008_hdtv_buyers_guide_part_1.php" target="_blank">2008 HDTV Buyers Guide</a>, which should help you with that subject.  <p>The market of larger screens with stunning image quality has motivated more people to migrate to bigger-and-better models, especially panels (ie flat screens), and they will eventually begin to appreciate the differences of image quality when viewing HD after switching from a regular channel. After that, there is no turning back; your favorite list of channels from the remote will be mostly HD.  <p>TV viewers that are less concerned about picture quality than they are about more free over-the-air channels can also benefit from DTV, due to the larger number and variety of channels DTV offers with the SD sub-channel capability.  <h2>The Digital Opportunity for Quantity vs. Quality</h2> <p>The current analog television uses quite a bit of bandwidth from a reserved spectrum of airwaves that are set aside for a limited number of TV channels in increments of 6 MHz. In general, DTV can efficiently maximize the use of that TV spectrum, to have more and better quality digital channels and services.  <p>Additionally, when analog TV is fully replaced by digital, part of the spectrum will be returned to the FCC, and the communications industry could use that spectrum to further benefit consumers with other communications business and technologies.  <p>Let us browse over a few DTV technical features that would benefit you and the industry:  <ul> <li><b>Multiple Standard Definition digital sub-channels<br></b> A single 6 MHz slot used for 1 <b>analog</b> channel has enough capacity to simultaneously support the broadcast of 4-6 standard definition (SD) <b>digital </b>sub-channels, to meet the requirements of certain demographic areas, ethnic channels, weather, children, etc., not to mention multiply the advertising revenue of broadcasters as well. In the analog world, those 4-6 sub-channels would have required 6 MHz <b>each</b>.</li> <li><b>High quality HDTV </b>can be broadcasted using the same 6 MHz channel-slot of one analog NTSC channel, and raise up to 9 times the resolution quality. The NTSC image is made of 480ix450 viewable picture elements per video frame (216,000). Each video frame is actually made by two half frames (interlaced fields) of 240 lines each (240x450 pixels on each field).<br><br>Most HD broadcast is also transmitted as <a href="http://www.hdtvmagazine.com/glossary.php">interlaced 1080i</a>, but with 2 million+ pixels x video frame (1080x1920 pixels), made by two half frames (fields) of 540 horizontal lines each (540x1920 pixels in each filed).</li> <li><b>Progressive video broadcast, unique to DTV</b> <br>A few HDTV networks broadcast HD using a progressive digital video format <a href="http://www.hdtvmagazine.com/glossary.php">720p</a>, as opposed to interlaced 1080i HD.<br><br>If one were to compare 720p vs. 1080i by the number of pixels that are spatially perceived by the eye when viewing one full video frame, 720p has half the spatial-resolution of the 1080i field-pair.<br><br>720x1280= 921K pixels on one 720p video frame compared to 1080x1920= 2+ million pixels on the 1080i frame.<br>The battle of opinion about which of the two formats (1080i or 720p) is better will never end, and it depends on the type of content, and the limitations of the display device.<br><br>For example, if your display device were a 720p panel limited to 1280 horizontal pixels, watching 720p content would be a perfect match, but you would never be able to see the full 1920 horizontal pixels of a 1080i image when you change to tune a 1080i channel (if all those pixels were actually recorded in the original content).<br><br>The same applied to legacy direct-view or projection CRTs due to their typical limited horizontal resolution. Beyond the interlaced vs. progressive image motion virtues, the 720p format itself has 33% less horizontal resolution compared to 1080i (1920-1280=640, 640 is 33% less than 1920).<br><br>The 720p progressive format does not have the problem of the interlace artifacts of 1080i. Each frame of 720p lines contains the complete detail of a full image frozen in time by the camera, and the frames are displayed at a higher speed (60 <u>frames</u>-per-second, rather than 30 frames of 1080i displayed as 60 <u>fields</u>-per-second). Progressive is better for fast content, such as a sports program on a dedicated sports network (eg. ESPN-HD).<br><br>However, let us look at the viewing experience in the home switching channels. If a mixed-content network decides for 720p transmission in a constant basis (such as ABC), the progressive format might be beneficial when broadcasting a sports program (faster frames could be more important than more detailed lines on that fast content), but movies and documentaries on that channel would also be broadcasted at the limited horizontal resolution of 720p (1280), when a 1080i channel could make the image of that content look more spatially detailed with the full 1920 horizontal pixels.<br><br>In general, a broadcast format that is good for you, for the type of content you watch, and for your display resolution, might not be the situation of your neighbor. It depends on the content you and he/she watch more often.<br><br>People do not choose a TV set based on the resolution of the broadcast channel they watch more often, but if so, since only a few networks use 720p, and the TV market is gradually moving to a larger variety of 1080p resolution TVs for even medium size screens, the option of 1080x1920 displays becomes more beneficial as time progresses, especially with the Blu-ray media using all that resolution.<br><br>I personally use my projection home theater for mostly movies on a 135-inch CinemaScope screen. The 33% horizontal resolution gain of the 1920 horizontal pixels of 1080i is more evident on it, and even when an original 720p program is converted to 1080p for display, the lower spatial resolution of the original 720p signal is evident on the conversion to 1080p, because pixels that never existed in the incoming image cannot be invented by video processing to look the same as if they were original, especially during motion, and more obvious when displayed on a very large screen.<br><br>I must add that there are two other HD progressive formats within the 18 formats of DTV, and that is 1080p at 24 and 30 frames per second. Broadcasters are not currently using these formats to transmit HD to homes but some satellite companies are moving in that direction. The 1080p24 format is suitable to 24-frames movie film content when transferred into a pre-recorded media such as Blu-ray. For more detail check the <a href="http://www.hdtvmagazine.com/glossary.php" target="_blank">HDTV Glossary</a> or the <a href="http://www.hdtvmagazine.com/articles/2006/01/why_1080p.php" target="_blank">1080p articles</a> I wrote about the subject.</li> <li><b>Digital allows for a combination of HD and SD sub-channels</b> sharing the same 6 MHz channel allocation. When HD is compressed with MPEG-2 at 19.4 Mbps it typically needs the whole 6 MHz bandwidth of the channel for itself.<br><br>Even then, some rapid content such as strobe flashing, sudden flames, or waterfalls, could show with some artifacts. Sharing the 6MHz channel-slot space with other content would cause the 19.4 Mbps bit rate of the HD content to drop, and the HD image quality to suffer.<br><br>It is important to emphasize that HD quality is generally the main reason most consumers have when purchasing a large screen HDTV, to view large stunning HD images, not just digital anything.<br><br>However, even when I am not in favor of subtracting from the quality of an HD image to give space to additional SD sub-channels, sometimes it might be necessary, and the point here is that the digital technology allows it, analog did not.<br><br>PBS is an example. Although their HD sub-channel shows the impact of the reduced bit-rate, PBS made the decision to simultaneously broadcast other parallel SD channels for family, children, etc. as a public service.<br><br>In perspective, the analog alternative would have used 6 MHz <u>for each</u> sub-channel, which means that those channels might have never existed in the analog world due to the limited space in the TV airwaves spectrum.</li> <li><b>Digital compression (MPEG-2 for DTV)</b> allows for a digital signal to fit into a smaller space for recording or transmission purposes. The saved space could be used for other sub-channels or services. Compression can be flexible in a way that was not possible with analog NTSC.<br><br>Compression can also evolve with improved algorithms over time to be more efficient and allow the transport of more content over the same bandwidth, as satellite and cable did for years.<br><br>These subscriber services are now switching HD to MPEG-4 compression technology, which is more efficient than MPEG-2 (about 50%), however, the compression standard selected for broadcast terrestrial DTV was MPEG-2, not MPEG-4, and millions of integrated DTVs and tuning STBs since 1998 were designed to handle MPEG-2 compression, not MPEG-4.</li> <li><b>Mobile and portable devices</b> would eventually receive digital TV when (and if) implementing some recent technological advances, such as <a href="http://www.hdtvmagazine.com/articles/2007/08/mobile_dtv_reception_-_advanced-vestigial_side-band_a-vsb_-_the_system.php">Samsung's A-VSB</a>. The A-VSB system of transmission shares the same 6MHz assigned to the terrestrial DTV channel without interfering with terrestrial receivers.<br><br>A-VSB and other similar mobile digital transmission systems proposed for standard approval can generate new business opportunities for broadcasters. However, the bits needed for the digital mobile service need to be taken from the same bandwidth used for a good quality HD image (approximately 20% or more).<br><br>As with the case of implementing multiple SD sub-channels, the quality of HD could deteriorate if its bit rate is penalized. The opportunities that digital brings could be very tempting for quantity business models, so consumers should speak up if they have interest in quality, especially when HD content shows obvious image deterioration.<br><br>However, the point here is that the digital system is capable to offer parallel services sharing the same bandwidth.</li> <li><b>Dolby Digital multi-channel digital audio</b> was selected as the audio standard of DTV. The audio format is lossy compressed with 5.1 discrete channels (stereo L/R front, stereo L/R surround, and center channels) at their full 20Hz-20KHz frequency response range, and with a separate .1 LFE (low-frequency-effects) channel for a subwoofer.<br><br>Dolby Digital 5.1 is a considerable improvement compared to legacy stereo Left/Right and even so compared to the legacy 4-channel surround Dolby Pro-Logic.<br><br>By design, Dolby Pro-Logic has the surround and center channels matrix encoded into the two L/R channels, they are not discrete (not matrixed) channels as the Dolby Digital standard used on DTV.<br><br>Additionally, legacy Dolby Pro-Logic has no separate LFE subwoofer channel, and the surround channel is only monaural (not L/R stereo) and with a reduced frequency response, although is reproduced over two side/rear speakers (making you believe that are different channels).</li> <li><b>Digital can also facilitate the implementation of data casting</b> and of two-way digital interactive services that bring the opportunity of new business models to broadcasters. These services were not possible with uni-directional analog broadcast sending signals as out-only from the broadcast antenna.<br><br>As with the implementations of SD sub-channels and mobile digital transmission, this feature would also have to share the same 6 MHz allocation that otherwise could be used for a good quality HD channel.<br><br>How can you help? Monitor the quality of what you like to watch, let the broadcaster know your opinion, and educate others as well.</li></ul> <p>A non-technical benefit for DTV is that billions of dollars will become available from the auctioning of the spectrum of those 6 MHz parallel channels returned to the FCC after the switch to digital broadcasting.</p> <p>In addition to the potential of having that spectrum facilitate the implementation and modernization of other communication technologies that could benefit consumers, the proceeds of the auction would help pay for the digital-to-analog converter-box program, for programs to help first-responders, and for deficit reduction. </p> <p>Congress already set the auction of the spectrum in the 700 MHz band (January 2008). Since reportedly no bids met the FCC's $1.3 billion first auction's reserve price, the FCC was planning to run simultaneous auctions, "the first would be for the whole D-block at a lower reserve price of $750 million, while the second would be for 58 regional licenses, used for either LTE or mobile WiMAX" according to CedMagazine.  <p>In the <a href="/articles/2008/10/dtv_transition_-_can_you_help_part_3.php">next article in the series</a>, I will cover the subject of DTV market conditions for the transition, the number of DTVs vs. household's coverage for the DTV Transition deadline, and a projection for the full replacement of analog TVs in the US.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>October 23, 2008 09:18 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1526
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
 			<h2>More on Digital (DTV) Transition</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Digital (DTV) Transition'
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
 				AND entry_id <> 1526
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2008/10/dtv_transition_-_can_you_help_part_2_-_a_technical_view.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
