<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1406";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1406 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hqv benchmark, blu ray, dvd player, external scaler, digital video, test, DVD, dvd, player, video, tests, content, display, testing, response, hqv, HQV, Benchmark, benchmark, pixel, Blu, blu, ray, vertical, horizontal" />
	<meta name="description" content="HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, &lt;a href=&quot;http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789&quot;&gt;Scaler&lt;/a&gt;. 

The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of the tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to disc players..." />
	<title>HDTV Magazine Reviews - HQV Benchmark Blu-ray, DVD and HD DVD</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hqv_benchmark_blu-ray_dvd_and_hd_dvd';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HQV Benchmark Blu-ray, DVD and HD DVD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HQV Benchmark Blu-ray, DVD and HD DVD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>June 19, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HDTV Accessories">HDTV Accessories</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php&amp;phase=2&amp;title=HQV%20Benchmark%20Blu-ray%2C%20DVD%20and%20HD%20DVD&amp;bodytext=HQV%20Benchmark%20is%20produced%20by%20Silicon%20Optix%2C%20a%20leading%20developer%20of%20video%20processing%20technology.%20If%20you%20are%20new%20to%20the%20terms%20video%20processing%20and%20scaling%2C%20then%20a%20great%20foundational%20start%20is%20our%20own%20Video%20Dictionary%20on%20HD%20Library%2C%20%3Ca%20href%3D%22http%3A%2F%2Fwww.hdtvmagazine.com%2Fforum%2Fviewtopic.php%3Ft%3D3789%22%3EScaler%3C%2Fa%3E.%20%0A%0AThe%20HQV%20Benchmark%20series%20of%20discs%20have%20received%20a%20lot%20of%20press%20and%20a%20lot%20of%20players%20have%20failed%20to%20pass%20many%20of%20the%20tests.%20Let%27s%20take%20a%20look%20at%20what%20each%20of%20these%20tests%20are%2C%20what%20their%20purpose%20is%20and%20what%20it%20actually%20means%20to%20disc%20players...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><br clear="left"/><b>Product line up and pricing</b></p>

<ul><li>DVD: HQV Benchmark Version 1.4 NTSC $20</li>
<li>DVD: HQV Benchmark Version 1.4a PAL $20 (not reviewed)</li>
<li>Blu-ray: HQV Benchmark Version 1.0 $20</li>
<li>HD DVD: HQV Benchmark Version 1.0 $10</li></ul>

<ul><li>Bundle 1: Blu-ray, DVD (NTSC) $30</li>
<li>Bundle 2: HD DVD, DVD (NTSC) $25</li>
<li>Bundle 3: Blu-ray, DVD (PAL) $30 (not reviewed)</li>
<li>Bundle 4: HD DVD, DVD (PAL) $25 (not reviewed)</li></ul>

<p><b>Summary: </b>A very useful testing regimen if you understand the limitations<br /></p>

<p>Have you ever wondered why special features on DVD don't look as good as the movie you watched? Maybe you've wondered why some of your DVDs don't look as good as others? Or why Blu-ray Hollywood movies appear to have more detail than concerts or documentaries? HQV Benchmark has a testing regimen to help you figure all of that out!</p>

<p>HQV Benchmark is produced by Silicon Optix, a leading developer of video processing technology. If you are new to the terms video processing and scaling, then a great foundational start is our own Video Dictionary on HD Library, <a target="_blank"href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=3789">Scaler</a>. </p>

<p>The HQV Benchmark series of discs have received a lot of press and a lot of players have failed to pass many of its tests. Let's take a look at what each of these tests are, what their purpose is and what it actually means to your overall experience.</p>

<p><br />
<b>Testing, Scoring, Education</b></p>

<p>The test material concentrates mostly on deinterlacing and scaling. Silicon Optix provides a downloadable PDF file of the test regimen which includes how to score each test along with detailed explanations. While the guide provides comparison images, the resolution is not high enough for many of the tests to assist you in fully appreciating what to look for.</p>

<p><a target="_blank" href="http://www.hqv.com/contentEngine/dspDocumentDownload.cfm?PCVID=6557af58-7e90-e2a3-bea3-f6ec25bf8781">HQV Benchmark DVD Testing and Scoring Guide</a><br />
<a target="_blank" href="http://www.hqv.com/contentEngine/dspDocumentDownload.cfm?PCVID=6557b0fd-7e90-e2a3-bdde-f1edd6040515">HQV Benchmark Blu-ray and HD DVD Testing and Scoring Guide</a></p>

<p>Many of the DVD test results and all of the Blu-ray and HD DVD test results rely on perception requiring proper calibration of the display for valid results and is noted by * in the title. Neither disc provides a full suite of calibration test patterns. Digital Video Essentials is recommended and available in Blu-ray and DVD. There is also an HD DVD and DVD combo available while supplies last. If your system has been ISF calibrated, you are ready for testing. If not, <a target="_blank" href="http://www.isfforum.com/Find-a-Calibrator/ISF-Forum-Calibrators.html">check the ISF Forum</a> for a professional ISF calibrator in your area.</p>

<p><br />
<h2>HQV Benchmark DVD</h2></p>

<p>The test material is a mix of 4:3 and 16:9 original aspect ratio (OAR) content, which will require you to manually change the aspect because there are no flags to trigger the auto 4:3/16:9 aspect feature that some players support. If the auto aspect does not provide a manual feature then you are stuck with 4:3, which will not be correct for some of the 16:9 tests. The introduction and test material does not provide reference imaging quality for showing off the DVD format at its best.</p>

<p><b>Color Bars (4:3)</b><br />
This is a resolution test that is part of a multiple test pattern. This is not only one of the more useful patterns on the disc, but contains other tests such as color decoding at different saturation levels and luminance (video) levels. I recommend testing in both 16:9 and 4:3 aspects. Above the middle is a resolution response test for both luminance and chroma. Going left to right they are numbered as 4, 3, 2 and 1 with 1 representing 720 pixels horizontally. The catch is that this is a 4:4:4 encoded pattern which means the chroma has the same response as luminance. The consumer DVD standard (and HDTV) uses 4:2:0 encoding which cuts the chroma response to half of luminance to conserve bandwidth and storage space. The chroma response in block 1 serves no purpose since it cannot be properly reproduced so disregard those results. This pattern is part and parcel of confirming proper calibration prior to testing.</p>

<p><b>Jaggie 1 (16:9)</b><br />
A single bar is constantly rotated inside a circle testing the high detail b/w video or luminance portion of an image. </p>

<p><b>Jaggie 2 (16:9)</b><br />
Three bars move back and forth in a narrow arc, covering 50-20 degrees, inside a circle testing the high detail b/w video or luminance portion of an image.</p>

<p><b>Flag (4:3)</b><br />
A final jaggie test of both color and luminance using the common and notoriously difficult American flag waving in the wind.</p>

<p><b>Detail (16:9)</b><br />
The disc narrative and guide both stress that this material should "exhibit fine detail resulting in a crisp realistic image" and other similar statements. This material will never have the response one would expect or can get viewing properly captured and mastered DVD video. Page 10 of the guide compares two images that hardly look different for a test score high of 10 and 0, and in this case resembles reasonable expectations for this test.</p>

<p><b>Noise (4:3)</b><br />
If the TV or player does not have a noise reduction (NR) feature, skip this test; it is not about players. MPEG NR targets compression noise, is processed differently and should not be used for this test. A series of 12 still images are provided and most target the blue color channel. Two of those images never showed any noise and one was marginal. These images are great examples of a noisy analog cable service or a satellite / cable set top box delivered via channel 3 or 4 to your TV. This test is all about the NTSC broadcast television system, analog cable, analog RF tuners and the RF noise that can easily come from them. While having little to do with players, they are useful for DVD recorders and broadcast NTSC. For high fidelity with DVD, NR on your DVD player should be turned off. For recorders this feature may make some or all of your noisy channels more palatable but in most cases the setting will also apply to DVDs, in which case it should be turned off.</p>

<p>One point missing from the guide; the first step is to view the content with NR turned off and that includes the TV if available. Look through or ignore the noise and recognize the detail that is present. Now turn on NR on the player and see how much noise is removed along with any loss in detail. You can also reverse the test, turn the NR off on the player and turn it on for your TV if available. Some NR circuits offer different range levels in which case test all of them and determine which setting provides the best balance of detail versus noise suppression.</p>

<p><b>Motion Adaptive Noise (16:9 and 4:3)</b><br />
A 16:9 image of a roller coaster and a 4:3 image of a boat going down a river are provided. While an NR circuit can successfully navigate the prior noise test of still images, the addition of motion will show any artifacts created by the process and may help identify what kind of NR the video processor is using. Follow the same procedure for testing as previously described, following the guide for evaluation. The roller coaster is also a convenient test for LCD pixel speed provided you turn off NR on the player and display. </p>

<p><b>Film Detail (4:3)</b><br />
In the guide, this test is called 3:2 Detection, which is a far better description of the test result. A familiar movie scene is provided of an F-1 car passing by empty bleachers testing the standard 3:2 cadence required with 24fps (frames per second) content. </p>

<p><b>Assorted Cadences (16:9)</b><br />
A test clip is provided in 8 different cadences. This is the most brutal part of the testing regimen that few players or displays will pass. External scalers should pass most if not all of the following tests:</p>

<p>2-2 30fps film<br />
2-2-2-4 DVCAM<br />
2-3-3-2 DVCAM<br />
3-2-3-2-2 VARI SPEED Broadcast<br />
5-5 Anime<br />
6-4 Anime<br />
8-7 Anime<br />
3-2 24fps film</p>

<p><b>Mixed 3:2 with titles (4:3)</b><br />
4:3 images at 24fps are provided with the same kind of 30fps-based titles you would get from your broadcaster notifying you of weather alerts or emergencies. This is also delivered in the form of end credits at the end of a TV show. This applies far more to broadcast TV and your display rather than players.</p>

<p><br />
<b>HQV Benchmark DVD on Your Player</b></p>

<p>Just because a player fails some or all of these tests does not mean it will generate the same errors when playing a Hollywood movie on your player, nor would passing some of these tests qualify as high fidelity performance. None of the tests even relate to how the vast majority of movies are captured, processed and mastered for DVD along with how your player is designed to reproduce them. Missing from this disc is the same test material processed and mastered just like Hollywood does. Without such a reference point the person doing the evaluation may have unrealistic expectations of how well the test material should perform.</p>

<p>While it can be argued on the surface that some of these tests should apply to a player, as a reviewer I find myself in a catch 22. Test material from Avia, Video Essentials, Sound and Vision, Digital Video Essentials and nearly all popular movies look decent to fantastic although that same player fails all or some of the HQV Benchmark material. How can that be? And as reviewer, how do I report a passing or failing grade?</p>

<p>The key is understanding why an inexpensive DVD player can get decent results with no-name video processing. This is achieved during mastering by including progressive flags in the data directly from the mastering studio telling the video processor in the player how to take the interlaced fields and put them together for a proper 480p presentation. This is an extremely intelligent way to deliver a high fidelity performance envelope on the cheap! With a native 480p 16:9 display, typically CRT only, and a properly designed 480p player, you are in videophile nirvana due to this free ride but this is an HDTV world and most displays these days require the 480p free ride gets scaled to one of the HD scan rates, 720p, 1080i or 1080p. Proper deinterlacing is the crux and scaling is far easier so this free ride can provide decent to high quality performance beyond 480p depending on the design goals! There is a catch; no flags, no free ride and with incorrect flags, the free ride could turn bumpy with errors/artifacts.</p>

<p>The HQV Benchmark DVD technical twist is that the material is encoded as raw 480i, no progressive flags for dumb scaling, leaving the player entirely on its own to figure out how to deinterlace the content. The bottom line is that the vast majority of players are going to fail many of the tests that relate to DVD content. While the movie is bound to have these progressive flags, that may not be the case for special features. This has improved over the years, but for a movie buff and/or DVD collector much of a library is going to contain such content. On top of that, many a collection will have 4:3 letterboxed releases along with the oddball cadences that come with low volume or low budget productions, cult classics, anime and TV shows on DVD. Some folks desire a player or external scaler that can get the most out of such content. HQV Benchmark is exactly what the doctor ordered for reviewers and videophiles alike who are looking for a simple straight forward battery of tests to quickly determine performance with such content. The only test missing is for 4:3 letterboxed material. </p>

<p><br />
<h2>HQV Benchmark Blu-ray / HD DVD</h2></p>

<p>The introductory scenes of 16:9 video content are quite short, although in the other chapters about testing there is material of greater length. This content is worthy of overall image quality evaluation, but it does appear slightly soft in detail and flat in dynamic range compared to other reference material. Both discs provide the test materials in three different play formats; Play Loop, Play All Tests (manual advance) and Select Individual Tests. The first two automated play formats unfortunately skip one or more tests (noted in the test pattern breakdown) so if you want to view them all then choose Select Individual Tests.</p>

<p><b>HD Color Bars</b><br />
This pattern is only available under Select Individual Tests and appears to be derived from a non HD source and scaled to 1080i based on the color pixel errors at the edges where two colors meet. Useful for checking / confirming luminance and color levels prior to testing.</p>

<p><b>HD Noise</b><br />
Provides two images, a flower and sailing boat, which have motion components as well as noise. The Blu-ray version skips the sailing boat when choosing Play Loop. You may be hard pressed to even use this test since a noise reduction feature is not common for HD disc players or displays when viewing HD scan rates. Please check the DVD version of this test for additional details. </p>

<p><b>Video Resolution Loss</b><br />
A SMPTE RP-133 1920X1080 <a target="_blank" href="http://www.isfforum.com/viewtopic.php?t=82">1:1 pixel map</a> of luminance, b/w video, has a 360 degree constantly rotating bar. The results as described in the guide require a 1920x1080 pixel matrix to test the internal scaler along with proper aspect setting of the display for 1:1 pixel mapping best confirmed prior to testing with the same pattern from Digital Video Essentials. This pattern has a black and white video level error and pixel mapping error. Black is about 10% above peak black and white is about 10% below peak white; this is not critical to the test. The pixel map is correct for the vertical plane, 1080 pixels, but incorrect for the horizontal plane, 1920; this is not critical for this test of 1080 vertical resolution but the 1920 burst for horizontal resolution will not show up appearing as a gray box. As a 1:1 pixel map pattern, the horizontal response should be ignored. This test applies to other pixel matrixes and 1080i content but you will likely have problems with the vertical 1080 burst and the response may vary in the five different areas of the image. The rotating bar will tell you if the 3:2 cadence is being properly detected and should show smooth motion as it spins.</p>

<p><b>Diagonal Filtering Jaggies Test</b><br />
Provides the same two tests as the DVD. The first is the single bar constantly rotating and the second is where three bars move back and forth in a narrow arc. The Guide calls this Video Reconstruction Tests, does not document the second test and both disc versions require you choose Select Individual Tests for the second jaggie test to be viewed.</p>

<p><b>Film Resolution Loss</b><br />
A SMPTE RP-133 1920X1080 <a target="_blank" href="http://www.isfforum.com/viewtopic.php?t=82">1:1 pixel mapped</a> test pattern is panned back and forth along with a horizontal pan of a football stadium. The Blu-ray version skips the stadium when choosing Play Loop. The test uses the same pattern as Video Resolution Loss containing the same errors and has the same requirements for testing. Unlike the previous test, this one can show horizontal response errors due to motion. As before, the 1920 box will be gray. Included in this pattern are video level boxes with percentage of modulation and you can see that the one pixel space between the two digits is missing implying that frequency response has been slightly extended. This means that the 1920, 960 and 480 boxes actually have slightly higher pixel counts which is why it does not pixel map and probably why the 1920 response is missing. This test still applies but due to this error it clearly is not the ultimate test it could be and who knows if a scaler might perform better with a true pixel map expecting 1920x1080. That said, the guide and disc only discuss errors in the vertical response. </p>

<p>The stadium test follows but lacks the snap of detail expected from quality capturing and 1:1 pixel mapping. It only pans right to left and one display that was tested with the SMPTE pattern had less horizontal response artifacts in that direction over the other. It's a shame the stadium was not panned in both directions like the SMPTE pattern. When the 50 yard line hits about the center of the screen there is a subtle pixel response change and a change of light output level in some areas of the scene. Two displays failed the SMPTE pattern test in clearly different ways for either the horizontal or vertical planes. I found this test one of the most difficult to analyze. </p>

<p>With either display the horizontal plane detail would drop in and out at what appeared to be the same points. The display that passed the prior test for vertical response simply had more detail while in motion and at the final still point of a few seconds. While the other display faired far better with the prior test of horizontal response, both appeared to respond equally. With a casual viewing you may be hard pressed to detect any appreciable difference in the stadium pan. If you concentrate on the horizontal response you will miss the point of this test. Concentrate on the vertical response or horizontal lines in the bleachers, light towers, field and elsewhere in the image. Those are the trees you are looking for in this forest and the horizontal response of vertical lines seems best left ignored.</p>

<p><br />
<b>HQV Benchmark Blu-ray / HD DVD on Your Player</b></p>

<p>As with the DVD version, missing from these discs is the same test material processed and mastered just like Hollywood does. A 1080p24 reference point with a proper 1080p24 display would show the viewer what correct performance looks like assisting the 1080i evaluation. It would be interesting to see how a player handles converting a Hollywood 1080p24 version of these specific test materials to the other scan rates. </p>

<p>Just like DVD, we have the same dilemma of how a player can fail these tests yet show not one sign of trouble with actual movies or calibration and test discs like Digital Video Essentials. The answer is the same; HD disc movies, for the most part, are not mastered as 1080i, they are mastered as 1080p24 and the player is designed to work with that when deriving other output scan rates. </p>

<p>Like DVD, special features still receive little to no attention and can vary from 480p all the way through 1080p24. I have even seen 480p 4:3 letterboxed content. Even though Blu-ray is an HD 16:9 destined format you just don't know what you are going to get. It doesn't end there; feature material that was captured as native 1080i is mastered as is. Most of those titles are concert, documentary features and television programming along with some indie movies with the common thread being that they were captured with HD 1080i based cameras. If the content is original 24 frame film then telecine mastering is performed at 1080p24 and that represents the majority of the Hollywood catalog. </p>

<p>Testing HD disc players is not nearly as straight forward as it was for DVD. Getting acceptable imaging out of lower resolutions (such as standard definition) is far more difficult. With DVD content you can run into some oddball content that will create very noticeable artifacts far more easily detected, even at far viewing distances. The nine cadence tests for DVD are a prime example and are not part of the HD version. With that in mind, the most critical HQV test for any HD disc viewer is proper cadence detection of the rotating bar of the Video Resolution Loss test which must pass with smooth motion and should be tested at 720p, 1080i and 1080p depending on what HD scan rates your display will accept. You may find that only 1080i provides the correct response. As for the other tests of 1080 horizontal lines testing vertical response, failure hardly means your image will stink but it does mean that 1080i content will have a loss in horizontal line detail. More than likely the failure will be due to vertical filtering which requires a deeper understanding of burst testing, what is going on and how it affects specific elements of an image. I refer you to <a target="_blank" href="http://www.hdtvmagazine.com/columns/2008/07/hd_waveform_vertical_and_horizontal_filtering.php">HD Waveform - Vertical and Horizontal Filtering</a> for detailed information.</p>

<p>For those with a display that only accepts a 1080p60 or 1080i output you can test the player's ability to convert Hollywood 1080p24 to either of those scan rates by using the SMTPE RP-133 from Digital Video Essentials for both Blu-ray and HD DVD. In this case the pattern is properly pixel mapped in both horizontal and vertical planes but it is a still pattern and cannot test for cadence or loss of resolution due to motion. For either scan rate setting you are looking for single pixel lines in the vertical 1080 box response and horizontal 1920 box response.</p>

<p><br />
<b>Vertical Filtering - Finding the Right Recipe</b></p>

<p>If you want every last shred of detail with 1080i content then vertical filtering is unacceptable. The easiest route to overcoming that is no recipe at all, which requires a player to properly deinterlace 1080i and convert it to 1080p60 or 1080p24 for a proper response along with advanced features that would allow you to output native 1080p24 content untouched. Most 1080p displays these days provide 1:1 pixel mapping at 1080p60 and many include 1080p24 as well. More importantly, the player must do this automatically providing a convenient hands-off and worry free approach for the user. While such a player would provide the ultimate keep it simple solution for any viewer, finding one that has been designed much less reviewed for this attribute may be far more difficult. For current Blu-ray players the right recipe of display and player can achieve the exact same results and external scaling provides yet another solution.</p>

<p>The discs were tested with a Sony PS3 and Toshiba HD-A35. The PS3 will not do a thing with native 1080i content except pass it along as is; testing 1080i conversion of the player to 1080p was impossible. With a full featured 1080p display the PS3 defaults to native output of the source. I did find it curious that the HQV Blu-ray menus are native 1080p24. The Toshiba on the other hand does not support native output and follows what you have set it for; 720p60, 1080p60 or 1080p24. Set for 720p or 1080p60 the Toshiba failed to pass the vertical 1080 box. Set for 1080p24 the Toshiba completely wiped out on cadence with every one of the tests creating a strobing effect at all times. Both players passed the Digital Video Essentials SMPTE RP-133 test at 1080i and 1080p60. A <a  target="_blank" href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000</a> and <a target="_blank" href="http://www.hdtvmagazine.com/reviews/2007/07/benq_w10000_1080p_dlp_front_projector.php">BenQ W10000</a> front projector were also tested. The Panasonic passed with flying colors for the most part while the BenQ failed due to vertical filtering.</p>

<p>The combination of the Sony PS3 and the Panasonic PTAE-1000 provides optimal results. Since the Sony defaults to native output of disc content and the Panasonic passes 1080i testing along with native 1080p60 (1080p 30 frame source) or 1080p24 support you are getting full automation for the best results. Either the internal scaler of the display is deinterlacing and scaling or is getting a direct feed bypassing it providing a 1:1 pixel map instead. </p>

<p>The Toshiba HD DVD player on the other hand does not have a native mode and simply does what you tell it to. If you know the HD DVD disc has native 1080i content then even with a display like the Panasonic you will have to change the output scan rate to match the disc source; same goes with the Toshiba feeding an external scaler. While lacking automation you can get optimal results manually.</p>

<p>The BenQ is a stellar performer and nearly a reference for video standards when pixel mapped at 1080p60 or 1080p24 yet 1080i is its Achilles performance Heal. Neither player tested provides a direct solution alone. One solution requires an external scaler with advanced features that will de-interlace and scale 1080i while allowing a 1080p60, 1080p30 or 1080p24 bypass. Mated with the PS3 you would achieve simple automation.</p>

<p>Untouched native 1080p24 content from player to a 1080p24 display is quite easy to acquire and will satisfy most users. In the end it all comes down to you, your system, how you use it (viewing distance) and the importance you place on some or all of the content you are viewing. Ultimately HQV Benchmark is limited in its ability to answer all of these questions if you want the most out of every bit of content you might be feeding your system since it is limited to 1080i testing only. </p>

<p><br />
<b>HQV Benchmark on Your Display</b></p>

<p>A properly designed 480i DVD player can be used to evaluate the internal scaler of a display or external scaler using the analog component inputs. Digital video, HDMI/DVI, is typically limited to 480p but if your display accepts 480i and your player can provide it without artifacts then that would be a valid test. Simply set the output of the player to 480i. Testing S-video and composite video connections is a bit more dicey because that requires proper down conversion so while likely not the ultimate reference test signal from your player there are still things that can be learned. For cable and satellite boxes using the analog component input of your display, these tests have direct value if you are setting the box to native so NTSC is output as 480i. Since you can't test the box it won't help you determine if the box or your display is doing a better job. Unfortunately, what this disc can't test is your NTSC TV tuner. Keep in mind during your testing how some displays apply, lock or limit different video processing features based on the input type. </p>

<p>A properly designed Blu-ray or HD DVD player can be used to evaluate how the internal scaler of a display or external scaler handles 1080i content using the analog component inputs or HDMI/DVI (with the player set for 1080i output). </p>

<p><b>Conclusion</b></p>

<p>While the some of the tests represent a small portion of the available catalog, the DVD version helps those seeking videophile nirvana with any and all DVD content on the planet via a player or external scaler. All of the tests are useful for determining how your display or external scaler handles broadcast NTSC video. For player evaluation, it should be considered a secondary test to the primary test of other discs that do follow Hollywood mastering representing the vast majority of what you would rent or purchase. The Blu-ray and HD DVD version provides a battery of tests to evaluate how your display or an external scaler handles HDTV 1080i content from Blu-ray disc or broadcast HDTV. If Blu-ray Hollywood movies are your main concern then just like DVD it should be considered a secondary test to the primary test of other discs that provide a native 1080p24 response representing the majority of Hollywood features. Either version of HQV Benchmark is unique in what it brings to the videophile table for display and player evaluation. With those limitations understood, the HQV Benchmark series is a unique evaluation tool that deserves a place in the videophiles toolbox.</p>

<p><b>Test Results of Past Products </b></p>

<p><a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33688#33688">Panasonic PT-AE1000U LCD Front Projector </a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33689#33689">BenQ W10000 DLP Front Projector</a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33709#33709">Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</a><br />
<a target="_blank" href="http://www.hdtvmagazine.com/forum/viewtopic.php?p=33710#33710">OPPO DV-981HD Upconverting SD DVD Player</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>June 19, 2008 12:24 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1406
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
 			<h2>More on HDTV Accessories</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HDTV Accessories'
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
 				AND entry_id <> 1406
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/06/hqv_benchmark_blu-ray_dvd_and_hd_dvd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
