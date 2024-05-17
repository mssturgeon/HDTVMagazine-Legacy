<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1626";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1626 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (8) {
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
	<meta name="keywords" content="dvd audio, frequency response, hqv benchmark, waveform monitoring, sonic signature, player, test, audio, OPPO, video, oppo, pass, response, DVD, dvd, PASS, color, Audio, Toshiba, disc, toshiba, analog, frequency, jvc, performance" />
	<meta name="description" content="The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection since the means to see it would infer a means to steal it. At this time the Panasonic PTAE-1000U has been kept in the stable just for the purpose of using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, preventing me from verifiying the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and are noted. All tests were performed using Digital Video Essentials as the source material." />
	<title>HDTV Magazine Reviews - OPPO DV-983H Upconverting DVD Player - On the Test Bench</title>
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

		$base_url = strleftback(PHP_SELF, '/') . '/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('OPPO DV-983H Upconverting DVD Player - On the Test Bench'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">OPPO DV-983H Upconverting DVD Player - On the Test Bench</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>January  8, 2009</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Upconverting DVD Players">Upconverting DVD Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php&amp;phase=2&amp;title=OPPO%20DV-983H%20Upconverting%20DVD%20Player%20-%20On%20the%20Test%20Bench&amp;bodytext=The%20very%20ability%20to%20inspect%20and%20view%20an%20HDMI%20video%20source%20goes%20directly%20against%20the%20copyright%20capability%20of%20the%20connection%20since%20the%20means%20to%20see%20it%20would%20infer%20a%20means%20to%20steal%20it.%20At%20this%20time%20the%20Panasonic%20PTAE-1000U%20has%20been%20kept%20in%20the%20stable%20just%20for%20the%20purpose%20of%20using%20the%20Wave%20Form%20Monitor%20feature.%20While%20the%20Wave%20Form%20Monitor%20does%20suffer%20when%20looking%20at%20high%20frequency%20response%20video%20such%20as%20bursts%2C%20it%20is%20also%20the%20perfect%20tool%20for%20checking%20IRE%20levels%20and%20color%20decoding.%20This%20does%20come%20with%20the%20limitation%20of%20only%20being%20able%20to%20check%20YPbPr%20output%2C%20preventing%20me%20from%20verifiying%20the%20switching%20to%20RGB%20output%20that%20would%20be%20required%20for%20a%20DVI%20input.%20Some%20of%20the%20results%20are%20based%20on%20visual%20calibration%20checks%20as%20well%20as%20signal%20and%20are%20noted.%20All%20tests%20were%20performed%20using%20Digital%20Video%20Essentials%20as%20the%20source%20material.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">This portion of the review details how the OPPO DV-983H performed on the test bench. Please read the <a href="/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_review_essentials.php">OPPO DV-983H Review Essentials</a>, if you have not already.  <h2>On the Test Bench </h2> <p>The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection since the means to see it would infer a means to steal it. At this time the <a href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000U</a> has been kept in the stable just for the purpose of using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, preventing me from verifiying the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and are noted. All tests were performed using Digital Video Essentials as the source material.  <p>Comparison Players<br><a href="http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php">Toshiba HD-A35</a><br><a href="http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php">OPPO DV-981HD</a>  <h2>Digital Video Essentials</h2> <p><b>Video Levels</b>  <p>Whether by visual calibration or waveform monitoring, the player output 0IRE and 100IRE at the correct 16/235 levels.  <p><b>Color Decoding</b>  <p>Whether by visual calibration or waveform monitoring, the player output correct color decoding at HD scan rates, 720p, 1080i and 1080p (does not include 480p).  <p><b>Horizontal Frequency Response Luminance</b>  <p>As noted, waveform monitoring response was useless for this test. Visually the player passed the continuous frequency burst test quite well for luminance. For the low frequency pattern there is banding for the highest frequency burst. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly and this player is no exception. This pattern always has banding so the best I can state on this is high, medium or a low contrast response with high being the best and low being the worst. For the OPPO DV-983H a high contrast response was the norm; equaling our current reference, the Toshiba HD-A35.  <p><b>Vertical Frequency Response Luminance</b>  <p>Vertical frequency response was excellent in 720p, 1080i and 1080p.  <p><b>Frequency Response Color</b>  <p>While some banding is normal, the DV-983H showed a much higher level of banding than any other player reviewed so far. The contrast levels also dropped for the right one third of the response, the higher frequencies. The Toshiba HD-A35 remains a reference for this test.  <p><b>CUE, Chroma Upsampling Error</b>  <p>This causes a vertical breakup of color detail in the vertical plane, typically expressed in reds but can show up for other colors, and is related to the player using only one MPEG decoding method rather than both interlace and progressive and applying the correct version to the native source on the disc. The DV-983H passed.  <h2><a href="http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php">HQV Benchmark DVD</a></h2> <p>Color Bars (4:3) PASS<br>Color Bars (16:9) PASS  <p>Jaggie 1 (16:9) PASS  <p>Jaggie 2 (16:9) FAIL  <p>Flag (4:3) PASS  <p>Detail (16:9) PASS  <p>Noise (4:3) NA*  <p>Motion Adaptive Noise (16:9) NA*<br>Motion Adaptive Noise (4:3) NA*  <p>*uses block noise reduction related to MPEG compression  <p>Film Detail (4:3) PASS  <p>Assorted Cadences (16:9) <br>2-2 30fps film PASS<br>2-2-2-4 DVCAM FAIL<br>2-3-3-2 DVCAM PASS<br>3-2-3-2-2 VARI SPEED Broadcast PASS<br>5-5 Anime PASS<br>6-4 Anime PASS<br>8-7 Anime PASS<br>3-2 24fps film PASS  <p>Mixed 3:2 with titles (4:3) PASS  <h2>ABT Test Disc</h2> <p>You get a free ABT test disc to show off the capability of your player and of course the DV-983H passes all of the tests. Consider this a mini review. Similar to the HQV Benchmark DVD it, contains similar test material for jaggies, cadence and titles. I tested this disc on the OPPO DV-981HD and the Toshiba HD-A35. The OPPO DV-981HD passed many of the tests but it uses intelligent deinterlacing. The Toshiba failed horribly and uses dumb deinterlacing which so far infers the disc being played lacks progressive flags, which a dumb deinterlacing design depends upon. The ABT disc image quality and some of the tests like jaggies are better implemented and delivered than the HQV Benchmark.  <p><b>Scaling</b>  <p>I tested the OPPO at 1080p, 1080i and 720p feeding a 1080p DLP front projector that supports 1:1 pixel mapping with other scan rates. As expected the edges were soft which is a byproduct of the scaling process for nearly any manufacturer, although pixel-mapped 1080p output into a native 1080p will provide the sharpest response. Color bar patterns showed the typical dark edging where the different colors meet.  <p>Moving on to test images from DVE at 1080p I was greeted with an overall good response. The unit is not perfect and the DV-981HD and Toshiba HD-A35 have an edge. As noticed for the color burst response test this player is lacking by comparison. Overall either of the other players mentioned faired better with the Toshiba remaining a reference. This loss of detail was most evident in the restaurant sequence during the table view. The young man has a desert plate with a garnish of cut strawberries. The Toshiba provided better detail and nuance with the DV-983H blurring that. The DV-981HD didn't fair as well but outperformed the DV-983H.  <p>The next disc up was Star Wars Episode II, a disc I have tested to no end over the last year with numerous products. As with the above, the response was good but lacked that edge of color detail that the Toshiba can deliver.  <p>I typically don't bring up some DVDs I have burned from a variety of old VCR tapes and laserdisc titles. Eventually one of these will fall into a player and the OPPO was no exception. I bring them up because the OPPO would lose cadence lock if a pause, FF or RW function was used causing a choppy, strobing effect. Going in and out of the modes might get it to lock again. I have never had this problem with any other player.  <p><b>Scaling Special Features and Oddball Cadences</b>  <p>Typically I have two paragraphs describing limitations with such content but it is exactly in this area that the DV-983HD shines. What other players trip up on, this one will pass without a hitch. It passed all but one of the HQV Benchmark cadence tests! If you are a DVD collector looking for the best overall response with ANY content the DV-983HD has you covered. Just like an external scaler the OPPO delivers the goods yet for detail an external scaler has an edge much like the Toshiba HD-A35.  <p><b>Aspect Ratio Control</b>  <p>The DV-983HD provides an auto 16:9/4:3 switching mode so the player maintains correct aspect with special features or 4:3 movies, black side bars. OPPO calls this 16:9 Wide/Auto and is found in the setup menu.  <p>For the DVD collector with 4:3 letterboxed content this player performs quite well when expanding such content to fill out your screen. Again the player is directly competing with an external scaler in this capability.  <p><b>Additional Video Features</b>  <p><u>Y/C Delay</u><br>With a calibration disc you test for this error on your display and correct it.  <p><u>CUE Correction<br></u>Although the player passes the CUE test there is also ICP, Interlaced Chroma Problem. From the manual, "ICP is caused by encoding interlaced video so you may encounter it on some DVDs". Beyond the specific CUE patterns on the ABT test disc I did not see this feature change anything and it would seem leaving it in automatic will give you the best results.  <p><u>Video Mode</u><br>If you are playing back a PAL encoded DVD you can select the front end MPEG video decoder, Video 1, or use Video 2, the Precision Scaling and RightRate video processing technologies of the Anchor Bay processor. This feature was not tested.  <p><u>Color Space</u><br>Auto is based on information from your display during the HDMI handshake. You can force YCbCr 4:4:4 color space (480i), RGB Video Level (HDMI) and RGB PC Level (DVI). Useful if your display EDID is incorrect or missing in action for automated settings.  <p><b>Audio Performance</b>  <p>The OPPO provides full bit stream or multichannel PCM support for digital audio connections. Being a simple matter of set-and-forget, there was nothing to test. On the other hand it does provide 8-channel analog audio with 24/192 D/A converters and supports SACD and DVD Audio. I tested the PCM stream using a Denon AVR3808Ci A/V receiver which provides 24/192 DA conversion for the outputs. In this mode I was able to duplicate all DVD Audio formats out to 24/192. When an SACD is played the Denon indicates 88.2 kHz. Whether in multichannel or stereo mode SACD lacked in clarity not only from being down converted* to 88.2 kHz but also conversion from DSD to PCM. Ultimately, getting the full potential of SACD performance is an audiophile concern and I suggest an audiophile stand alone player. I know that is not an easy or inexpensive product to find. It is unfortunate but in the end maintaining a pure unconverted signal for SACD from source to decoded analog output, whether that be stereo or multichannel analog or digital, is a huge challenge for the end user on a budget.  <p>The player has earned some kudos as an audiophile product so I am going into more depth for this aspect of performance. Testing of the analog outputs takes us to my 2-channel system which is composed of custom and modified products designed and setup for the ultimate expression of a neutral audio signature. The only connections for either player during testing was the power into a PS Audio Power Plant 300 and audio connections to the preamp. The reference point is my modified JVC XLV720 for DVD Audio. The core of this test is simplistic D/A conversion performance of the analog outputs and therefore limited to 24/192 DVD Audio in stereo mode only.  <p>Make sure you turn on Audio Only mode as it makes a huge difference in the overall delivery. Note that when turning it on there is a delay before the feature is implemented and shows up on the display. Turning the video back on is instant. Unfortunately you can't use this feature with a movie so you will be taking this performance hit if you are using the analog outputs. Turning off the display provides marginal improvement. The player does have an OFF selection for the display turning it on when a feature or operation has been accessed and back to off automatically after a short period.  <p>Sonic Signature: The signature is robust and thick; all sonics have a heavier sound than normal as if the harmonics below the primary tone are being accentuated, best described as more of everything. Many a listener will be drawn into this signature because on the surface more is always perceived as better. This sonic signature covers up the nuances of tone and harmonics that are otherwise heard with the JVC. The sonic signature can complement a system that is the opposite; thin and strident. This also tilted the comparison because everything was perceived as louder than the JVC. On one test I used a higher volume setting for the JVC to counteract this anomaly yet that just made the JVC sound even better! Ultimately, while perceptually and euphonically pleasing, the DV-983H was not accurate.  <p>Sound Stage: With the video circuits turned on the soundstage is significantly pulled to the center. Even with the video circuits turned off the soundstage was far narrower than the JVC. Overall the playback had that in-your-face character. Most noticeable was a lack of space and depth. The OPPO delivered a disconnected <em>you are here and the music is there</em> experience rather than the commingling <em>we are one experience</em> of the JVC. The OPPO sounded contained; the JVC was limitless without boundaries enveloping the listener in a perceptual surround experience.  <p>Considering the price of the player, top notch audiophile analog performance is not a reasonable expectation. If you are an audiophile seeking audiophile stereo or multichannel analog performance outputs you will have to look elsewhere. Most will find the analog output satisfying and experience the improvement of comparing CD to SACD or DVD Audio. A dedicated audiophile CD player could sound better overall! In the end the DV-983H provides a good entry level audio signature that is to be credited for a smooth response while not doing anything grossly wrong or irritating causing listener fatigue. On the other hand it does provide multichannel PCM streams via HDMI for a capable receiver and this is where some form of audiophile nirvana can be found with DVD Audio discs.  <p><b>Bench Testing Perspective and Subjective Experience</b>  <p>Overall the DV-983H performed quite well for all test patterns but one, the Chroma Burst. With subjective viewing of actual images the OPPO DV-981HD is one hair better and the Toshiba HD-A35 was five hairs better providing a more refined and detailed presentation. While visible on my downstairs reference system at 3 screen heights, there was no real difference on the upstairs casual system at 4-5 screen heights. Bottom line is you need a very refined system used at its maximum potential to have any concern over this. More importantly the strong suit of the DV-983H is not ultimate performance with the main feature but how it handles everything else you can throw at it!  <p>Most will find the player pleasing for audio while opening the door to the world of HD audio in the form of DVD Audio and SACD. Bear in mind that the digital connection with DVD Audio is your best route to sonic nirvana.  <p><b>References</b>  <p>*<a href="http://www.smr-home-theatre.org/surround2002/technology/page_07.shtml">Poking a round hole in a square wave</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>January  8, 2009 11:37 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1626
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
 			<h2>More on Upconverting DVD Players</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Upconverting DVD Players'
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

 		<?if (8 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 8
 				AND entry_id <> 1626
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Richard Fisher'
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
 				<h2>About Richard Fisher</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Reviews</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2009/01/oppo_dv-983h_upconverting_dvd_player_-_on_the_test_bench.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
