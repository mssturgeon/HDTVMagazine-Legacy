<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 586";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 586 AND placement_is_primary = 1";
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
	<meta name="keywords" content="light output, color space, dynamic iris, color temperature, transmissive lcd, color, light, response, projector, screen, calibration, output, while, video, lcd, LCD, level, performance, using, pixel, normal, contrast, see, dynamic, iris" />
	<meta name="description" content="Starting with the PT-AE700 720p projector, Panasonic has built quite a reputation around their extremely wide installation capability and inexpensive pricing using transmissive LCD technology for the last couple of years. Panasonic continues their 720p capability with the PT-AX100 while introducing new 1080p24/60 capability in the form of the PT-AE1000U for a mere $4,000 USD street price, which is expected to get even lower as the months pass. This has certainly been the year for new 1080p front projection below $5,000 USD.

Transmissive LCD projection technology is well over a decade old using red, green and blue LCD panels. No color wheel is required and therefore no concerns over rainbows; those are a DLP issue only related to the size and expense of 3 chip capability as well as supply and demand of the devices. In the early days..." />
	<title>HDTV Magazine Reviews - Panasonic PT-AE1000U LCD Front Projector</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/panasonic_pt-ae1000u_lcd_front_projector';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Panasonic PT-AE1000U LCD Front Projector'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Panasonic PT-AE1000U LCD Front Projector</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>May  7, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HDTV Projectors">HDTV Projectors</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php&amp;phase=2&amp;title=Panasonic%20PT-AE1000U%20LCD%20Front%20Projector&amp;bodytext=Starting%20with%20the%20PT-AE700%20720p%20projector%2C%20Panasonic%20has%20built%20quite%20a%20reputation%20around%20their%20extremely%20wide%20installation%20capability%20and%20inexpensive%20pricing%20using%20transmissive%20LCD%20technology%20for%20the%20last%20couple%20of%20years.%20Panasonic%20continues%20their%20720p%20capability%20with%20the%20PT-AX100%20while%20introducing%20new%201080p24%2F60%20capability%20in%20the%20form%20of%20the%20PT-AE1000U%20for%20a%20mere%20%244%2C000%20USD%20street%20price%2C%20which%20is%20expected%20to%20get%20even%20lower%20as%20the%20months%20pass.%20This%20has%20certainly%20been%20the%20year%20for%20new%201080p%20front%20projection%20below%20%245%2C000%20USD.%0A%0ATransmissive%20LCD%20projection%20technology%20is%20well%20over%20a%20decade%20old%20using%20red%2C%20green%20and%20blue%20LCD%20panels.%20No%20color%20wheel%20is%20required%20and%20therefore%20no%20concerns%20over%20rainbows%3B%20those%20are%20a%20DLP%20issue%20only%20related%20to%20the%20size%20and%20expense%20of%203%20chip%20capability%20as%20well%20as%20supply%20and%20demand%20of%20the%20devices.%20In%20the%20early%20days...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">As this article went to publishing it was <a target="_blank" href="/forum/viewtopic.php?p=25340#25340">reported at HD Library</a> that Panasonic is providing a $1,000 USD rebate and one member found this projector for $2,749 USD. This is nearly half the price of projectors that can do better and taking all of its performance points into perspective this price level definitely hits the bang per buck category of entry level gear representing great value!</p>
<img src="/images/products/panasonic-pt-ae1000u.jpg" alt="Panasonic PT-AE1000U" /><br />

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid">$5,999.00</td>
<td class="greygrid"><a href="/equipment/model.php?man=Panasonic&model=PTAE1000U">$3,795.00</a></td>
<td class="greygrid"><a href="http://www.amazon.com/gp/product/B000MJD43Y?ie=UTF8&tag=hdtvmagazine-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=B000MJD43Y">$3,799.00</a></td></tr>
</table>
<br />
Serial #SG6640008R<br />
Warranty: 1 year parts and labor<br />
<br />
<B>Summary: Entertaining big screen pictures at 1080p/24, with the widest installation capability for a front projector</B><br />

<p><br />
Starting with the PT-AE700 720p projector, Panasonic has built quite a reputation around their extremely wide installation capability and inexpensive pricing using transmissive LCD technology for the last couple of years. Panasonic continues their 720p capability with the PT-AX100 while introducing new 1080p24/60 capability in the form of the PT-AE1000U for a mere $4,000 USD street price, which is expected to get even lower as the months pass. This has certainly been the year for new 1080p front projection below $5,000 USD.</p>

<p>Transmissive LCD projection technology is well over a decade old using red, green and blue LCD panels. No color wheel is required and therefore no concerns over rainbows; those are a DLP issue only related to the size and expense of 3 chip capability as well as supply and demand of the devices. In the early days fill factor of the pixels was quite large, creating a screen door effect that could not be missed with less than 8 screen heights. In this area transmissive LCD has improved by leaps and bounds, becoming one of the hallmarks of the Panasonic line; no pixels to be seen at even unreasonably close viewing distances. This technology does come with one thorn though that has yet to be tamed, natural dynamic range. To produce black, transmissive LCD has to block the light trying to pass through it, which remains difficult and results in a poor black response. Transmissive LCD also takes a hit in peak light output due to inefficient pass through losses of the LCD panels. This absorption of light, regardless of image, also requires the LCD panels be constantly cooled lest they or their neighboring optical parts suffer a melt down.</p>

<p>One way to improve dynamic range is to employ an auto iris and manipulate the gamma response. Most call this a "dynamic iris". Many are using this technique to enhance the viewer's perception of dynamic range while also improving spec numbers for higher contrast ratios. For a proper ISF calibration, the feature must be turned off or the numbers will never make sense; the feature changes things that much. Many transmissive LCD products use this process to create a competitive perceived dynamic image. This inspired <a href="/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php">HD Waveform 10 - Dynamic Iris and Gamma</a>, which uses the PT-AE1000 for the measurements. Please see the above article for the specifics.</p>

<p><br />
<B>Common Features</B><br />
<ul><li>2 HDMI and 2 component video via RCA inputs, 1 S-video and 1 composite video input, and 1 VGA PC input</li><li>On the top side are two adjustments for vertical and horizontal lens shift. There is a side door containing your control keyboard.</li><li>Aspect controls vary depending on input type and scan rate. All inputs offer 4:3 or 16:9. S-video and composite video add AUTO, 14:9, ZOOM1, ZOOM2 and JUST. HDMI with HD scan rates adds H-FIT, V-FIT and ZOOM. Component with HD scan rates adds H-FIT and V-FIT.</li><li>The remote is a good size, fits the hand well while providing a back light feature when you are in the dark. It is programmable for accessing the basic features of other products.</li><li>Programmable sleep mode turns the projector off automatically by pressing the SLEEP button on the remote and selecting one of 7 fixed durations from 60-240 minutes.</li><li>Horizontal and vertical position adjust is provided and is not to be confused with lens tilt features as you are moving the image on the LCD panels.</li><li>Provides two lamp power settings: NORMAL and ECO-MODE.</li><li>Dynamic Iris with on or off switching</li><li>Lamp hours can be found in the customer menu</li><li>Serial input for external control of the projector</li></ul></p>

<p><br />
<B>Not-So-Common Features</B><br />
<ul><li>14-bit digital video processing</li><li>Provides 7 preset modes for controls and features plus 5 user memories</li><li>Clock and phase adjustments are provided to reduce artifacts or obtain 1:1 pixel mapping with PC or component HD scan rates</li><li>Color management allows 8 points of adjustment, although six is all that is required, and 3 user memories for your defined color space</li><li>Full color temperature and tracking controls plus more</li><li>HDMI supports both video and PC video levels via an HDMI Signal Level adjust</li><li>"Pure Color Filter Pro" for professional-level color reproduction in select modes</li><li>Horizontal and vertical manual lens shift</li><li>Powered 2x zoom and focus</li><li>Waveform Monitor. This is a truly unique and precedent setting feature allowing you to see your signal source including digital HDMI/DVI just like the pros! It displays in frame/field or line modes including Y, G, R, and B signals in both modes. You can select which line you want to view. Adjustment of video setup controls is reflected directly on the monitor. The manual does a decent job of explaining how to use this feature and how to make some adjustments but is far from complete. This allows you to see your signal and know conclusively what is coming from your source for video setup. In fact, you should be able to do a source calibration with this tool provided you do not have the projector calibrated professionally. Video setup adjustments are reflected and a calibration is going to be changing those which change the reference points of 0 and 100IRE for your contrast and brightness settings, as an example.</li></ul></p>

<p><br />
<B>Out of Box Performance</B><br />
The PT-AE1000 offers so much latitude in placement, making it a clear winner for point-and-play big screen. With the lens shift, zoom and patterns it can't take more than 1-2 minutes to have it ready for action at some convenient spot in your room.</p>

<p>There are so many features and options related to video setup in addition to the 7 presets provided that it made my head dizzy. Out of those seven, four of the presets engaged a function making it known by a motorized sound and a significant reduction in light output. The three presets having the highest light output are CINEMA3, NORMAL and DYNAMIC. I went with NORMAL and selected a color temp that appeared correct.</p>

<p>First up was King Kong on HD DVD. While I was mesmerized by my first 1080p experience, I also found my first problem; some of those yellow taxi cabs showed signs of clipping where bright yellow was clearly white instead. Next up was my gaming PC, which brought out the second problem: loss of clarity. I adjusted focus several times and then tried turning the iris off and that helped. Nonetheless I found myself looking for an expected level of sharpness and detail at the pixel level that could not be found. Going to a PC game though, I was floored and remained floored for days due to the level of detail provided with another 1,152,000 pixels to work with, yet I also found myself frustrated when using things such as forums and web surfing. Time for a calibration to hash out this experience!</p>

<p><br />
<B>Calibration and Performance</B><br />
Calibration Reporting follows a system developed by the ISF Forum called the ISF Display Chart. Some headings provide an embedded link to the ISF Forum for a complete description of the calibration parameter. The key consideration for this part of the review is to ascertain if the necessary controls and features exist to calibrate the display to video standards. In some cases a control or feature may not be present although the product meets that particular qualification for performance.</p>

<p>Pictures are taken with a digital camera, which has limitations of its own inducing artifacts that are not there. The main purpose of the pictures is to provide a reference for the review, regardless of quality, and provide a fair impression of actual performance as compared to other pictures to which they may be compared.</p>

<p><br />
<B>Optics</B><br />
Focus uniformity was even left-to-right and top-to-bottom, with slight depth of field focus errors on the edges which will change with the zoom setting. Adjusting focus or adjusting zoom to the end points would make the picture slightly change position, and while not severe it certainly was odd.</p>

<p><br />
<B>Pixel Visibility</B><br />
The definition of pixel visibility has two forms. One is the ability to see the individual pixel response on your screen and is related only to your ability to perceive the full resolution; this is good. The other is your ability see pixels on the screen related to the fill factor provided by the technology and that is the concern for this measurement.</p>

<p>For this projector there is practically no fill factor. There is little to be seen between the pixels even when you are within a foot of screen. With LCD you are focusing on the panels and they have enough depth that when you are setting focus there are three steps that appear to be in focus. Without clearly visible fill factor at the screen it could be difficult for the novice to discern which of the three is correct as the focus pattern itself has an artifact adding to the elusiveness of this adjustment; I chose the middle focus point. If your desire is short viewing distances and big pictures without being able to see the technology, this one excels.</p>

<p><br />
<B>1:1 Pixel Mapping (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=82">Definition</a>)</B><br />
The luminance, black and white signal, and chrominance, color signal, frequency response pattern goes out to the full 1920 lines, but it should be noted that 960 lines is the limit of chroma or color detail for the current HDTV system. The only source that can provide 1920 chroma detail is a PC.</p>

<p><br />
<B>HDMI 1080i / Component 1080i / Reference</B><br />
<img alt="HDMI 1080i / Component 1080i / Reference" src="/images/reviews/PT-AE1000/allBurst.jpg" /><br />
For HDMI the 960 line response is nice for red and possibly oversaturated, lacking for blue and at 1920 there are two errors. Looking at the luminance 1920 you see it lacks the contrast of the 960. There is a graying out of the response as the difference between peak white and black is reduced. Another artifact is that the white lines are fatter than the black ones. If you were here with me you would note that the light from the white pixels are actually leaking into the black ones reducing what is called intra-field contrast ratio. This is not the full story behind this observed response and I cannot tell you if it is a limitation of the technology or the circuitry feeding the LCD panels.</p>

<p>At 1920 you will also note the highly saturated red and blue chroma response bleeding the primary of red or blue in the 1920 pattern removing any trace of the secondary cyan or yellow. This was an odd response and during the review it was not as if this artifact reared its ugly head with video or PC graphics. As noted earlier, video does not support a 1920 chroma response so it would seem safe to say that this would not cause a problem with a video source. On the other hand, if PC graphics are going to be a primary source this could show up with the right image.</p>

<p>From experience this type of response is related to filtering of a multiple line response also representing a specific reoccurring frequency. I have calibrated and tested many displays that could not pass the multiple alternating black and white single pixel burst at all providing only a grayed out field in it's place, yet could pass (not a correct response mind you - but pass) a single pixel white or black line from a pin pattern or the Sencore focus pattern of alternating E's formed out of single pixels. This is yet another example of how just the right image may be required to show the error.</p>

<p>The component 1080i response does not have the color bleed error at 1920. At 960 it does not have the clear pattern of red and cyan or even the ghostly blue and yellow response of HDMI. The 1920 luminance response remains consistent with HDMI. Key point for this test is that there are projectors on the market that pass these patterns correctly via either connection type.</p>

<p>Looking at the reference response you can clearly see each and every line and pixel of either luma or chroma information. This particular reference also shows the difference in pixel visibility between a full 1920x1080 chip DLP and this LCD. On the component 1080i image I happened to catch a clear shot of the LCD pixels; as you can see the fill factor is overwhelming good. While one might suspect that the differences in how these images were captured could easily account for what you are seeing, for the record, they are a fair facsimile of what you would perceive with the naked eye. The lack of detail and soft response is inherent of transmissive LCD technology.</p>

<p>While the projector clearly does 1:1 pixel mapping, this test not only shows response errors but also provides clues as to why a full chip DLP, as an example, would outperform it for detail and sharpness. At the viewing position, you cannot clearly make out pixels or fill factor of the DLP any more than the LCD, yet the perceived difference in response for these patterns would be strikingly clear. With real images you would note a difference in sharpness and detail.</p>

<p><br />
<b>Overscan</b><br />
Via HDMI, 720p, 1080i, and 1080p all chopped off a few pixels on the right side. This also revealed an interesting observation; the projector remembers vertical and horizontal centering positions by scan rate. Via component analog video, 720p and 1080i had the same result with only a one pixel loss on the left side for 720p. </p>

<p><B>Calibration Notes</B><br />
The settings constantly interacted with each other from brightness and contrast to the color temp adjustments; just one single click or numerical change could slightly throw off the whole cart for another round of adjustments. I started with NORMAL and being dissatisfied with the overall results tried CINEMA3, but that was off even more and could only be drawn in using the coarse color temp adjust in the video setup menu. In COLOR1 I found a response very close to standards and this one also used the motorized light reducing gizmo. While not documented, I set this one up achieving a similar response as I had with NORMAL but with an even lower light output. This particular setting did not require a color space adjustment having been preset by the factory on target. For most of the settings the green and blue primaries were increased for additional (and clearly beneficial) light output to sell the product, but pushing the color temperature towards cyan prevents 100% accurate color and fidelity. Another concern was the unexpected lack of fine adjustment making it difficult to get precisely on target for color temperature and tracking. These controls were no different than others I have used in the past, yet they had a coarse response for this projector. I was unable to get an overall delta C response below one, although my 30 and 80 IRE targets were correct.</p>

<p><br />
<B>Gamma (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Definition</a>)</B><br />
The gamma response charts consist of a green line representing the target gamma of 2.2 and a red line representing the response of the display. The average gamma figure only has value when the lines match; otherwise your calibrator will look at the individual steps to identify and correct the problem.</p>

<p>NORMAL Gamma Pre-calibration<br />
<img alt="NORMAL Gamma Pre-calibration" src="/images/reviews/PT-AE1000/normalgammapre.jpg" /></p>

<p>NORMAL Gamma Post-calibration<br />
<img alt="NORMAL Gamma Post-calibration" src="/images/reviews/PT-AE1000/normalgammapost.jpg" /></p>

<p>With the iris turned off the gamma was actually not bad for pre-calibration. The contrast had to be turned down significantly to even-out the steps and open up the peak white from 90 to 100IRE. This compression of gamma at the top in most cases infers a large color temperature error as well; yet another reason to try and obtain a proper response.</p>

<p><br />
<B>Color Temperature and Tracking (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">Definition</a>)</B><br />
A raw 6500 Kelvin response chart may look nice but it does not reflect a specific color. Delta C is provided instead which shows how far off from D65 the response is. The target is less than 1. Less than .5 error is considered quite good approaching a reference response. RGB response charts are included providing a much better understanding of response errors. In a perfect D65 world all three colors would be flat creating a single line response at 100% for a flawless color temperature and tracking response.</p>

<p>NORMAL Delta C Pre-calibration<br />
<img alt="NORMAL Delta C Pre-calibration" src="/images/reviews/PT-AE1000/normaldeltaCpre.jpg" /></p>

<p>NORMAL D65 RGB Chart Pre-calibration<br />
<img alt="NORMAL D65 RGB Chart Pre-calibration" src="/images/reviews/PT-AE1000/normalRGBpre.jpg" /></p>

<p>NORMAL Delta C Post-calibration<br />
<img alt="NORMAL Delta C Post-calibration" src="/images/reviews/PT-AE1000/normalDeltaCpost.jpg" /></p>

<p>NORMAL D65 RGB Chart Post-calibration<br />
<img alt="NORMAL D65 RGB Chart Post-calibration" src="/images/reviews/PT-AE1000/normalRGBpost.jpg" /></p>

<p>While not provided when I looked at the data charts, I could see the light output dropped by half creating a significant loss; so significant that I looked up a few front projection displays for comparison and indeed the PT-AE1000 was rather unique. It is commonly known that calibration will cause a loss in light output and a 25% drop is not unusual when bringing the two stronger primaries back into spec. Looking at the RGB chart you can see that both green and blue output were dramatically increased over red. Red is the Achilles heel of all arc lamp based displays as it is the primary with the least amount of light output. All display products have a weak primary so no big deal. What this means for an arc lamp source is to increase light output for sales and marketing you turn up green and blue since they have more light output to offer which ends up pushing the color temperature towards cyan. This creates a fairly common marginal error and correcting it has marginal impact on light output. For the Panasonic LCD this was not the case. What we see here is a heavy handed boost of green and blue creating horrendous cyan errors to provide a competitive bright image in the market. While I did not measure the results I did return the contrast back to pre-calibration which helped boost light output quite a bit while keeping things under some control. I tried returning the color temp to preset creating a huge boost in light output but that made white clearly cyan shifted.</p>

<p>Delta C errors typically come in two peaks for many consumer displays. As mentioned earlier, the response was difficult to tame but I gave it another go and managed to squeak out the final shown here below with two strong peaks and a fairly good valley below 1, which also happens to be in the prime area of most video levels for an image. In the world of consumer displays this is an average response.</p>

<p>NORMAL Delta C Post-calibration (second attempt)<br />
<img alt="NORMAL Delta C Post-calibration (second attempt)" src="/images/reviews/PT-AE1000/normalDeltaCpost2.jpg" /></p>

<p>NORMAL D65 RGB Chart Post-calibration (second attempt)<br />
<img alt="NORMAL D65 RGB Chart Post-calibration (second attempt)" src="/images/reviews/PT-AE1000/normalRGBpost2.jpg" /></p>

<p>Look at all three RGB graphs and you will see red never flattens out and remains a humped response regardless. I will not say this was the last word on a proper color temperature calibration but looking at the forest rather than this one tree the meadow has more problems than this one.</p>

<p><br />
<B>Color Decoding (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=78">Definition</a>)</B><br />
Color encoding and decoding for real images creates a complex array of phase angles which can interact. It is possible to have correct color space and incorrect color decoding. Decoding is tested using patterns that provide complex phase angles. For this test I use the Sencore VP403 Color Decode, SMPTE Color Bars and the Accupel 100% and 75% Color Decoder patterns.</p>

<p>The product does not provide any means to isolate the red, green and blue color channels to professionally check color decoding.</p>

<p>The PT-AE1000 appeared to pass within the limitations and errors created when using color filters for this test.</p>

<p><br />
<B>Color Space (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=81">Definition</a>)</B><br />
Once color decoding is established then comes color space. There are various types of color space in the world with the American SMPTE C and European EBU being very similar and specified for standard definition mastering applications and broadcast studio monitoring. The new kid on the block is BT709 for HDTV which slightly expands the color space from standard definition. As to which one you should use or that I as a reviewer should reference has been debated heavily. For reviews I will be using HDTV BT709 color space. If the product provides color space management this also infers that you can calibrate for SMPTE-C or EBU if you desire unless stated otherwise.</p>

<p>HDTV BT709 Color Space Pre-calibration<br />
<img alt="HDTV BT709 Color Space Pre-calibration" src="/images/reviews/PT-AE1000/normalCSpre.jpg" /></p>

<p>HDTV BT709 Color Space Post-calibration<br />
<img alt="HDTV BT709 Color Space Post-calibration" src="/images/reviews/PT-AE1000/normalCSpost.jpg" /></p>

<p>For NORMAL using out of the box settings all three primaries fall way outside the HDTV color space. Currently this is being used as a sales marketing tool with tag lines of deep color, rich color, lifelike color, etc. While Deep Color is a new and valid technology, there are currently no sources available. This projector does not support the standard nor meet that vastly larger color space specification. In this case, and for a while to come, this is nothing but an artificial expansion of standard color space and while not accurate more or deeper color claims are perceptually valid.</p>

<p>This was by far the easiest alignment and it still wasn't all too easy. It provides this neat cursor that makes you think what you are adjusting is what you will get once you press enter but the projector goes through a processing sequence for your new target and you find that the target moved. Ultimately this became a song and dance of making a small adjustment and pressing enter to see where the target would end up. In the end I was able to achieve an overall satisfactory response but was unable to get the red on target for HDTV or EBU. If SMPTE-C is your target the red does line up quite well for that specification.</p>

<p><br />
<B>Y/C and RGB Color Timing (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=84">Definition</a>)</B><br />
It can be difficult to define the source of errors related to this element of performance. What we are looking for is a precision alignment of luminance and the color signal as well the individual red, green and blue channels within that color signal. When this is not set properly, edges form between color blocks reducing color definition as well as creating an artifact with the right images. For this test I use the Accupel color decoding pattern. Images do not reflect accurate color but you can still identify the primary or secondary colors.</p>

<p>Magenta and Green Error<br />
<img alt="Magenta and Green Error" src="/images/reviews/PT-AE1000/YCcolor.jpg" /></p>

<p>Reference Response for All Colors<br />
<img alt="Reference Response for All Colors" src="/images/reviews/PT-AE1000/YCcolorREF.jpg" /></p>

<p>The Panasonic shows a very clear and visible error with magenta and green in the 2-3 pixel range of size. The rest were fine. Note the error between magenta and green only occurs when magenta follows green (to the left of green) as well a difference in error between magenta and cyan depending on if it is on the right or left of green. These differences are not uncommon when an error shows itself. In the reference image you can slightly detect the same type of error between magenta and green, yet this was only 1 pixel in size with a marginal error in color allowing it to blend well, making it difficult to see from the viewing position.</p>

<p><br />
<B>Edge Enhancement (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=79">Definition</a>)</B><br />
Out of the box, the display had the notorious outlining of edges, but this was eliminated using the customer controls. This also brought the lack of detail to light. Without nice sharp edges coming from the pixels themselves, a correct setting created a soft look. I did find just a glimmer of edge enhancement helped with this perception as well as improved legibility with PC text.</p>

<p><br />
<B>Multi-source Ready (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=83">Definition</a>)</B><br />
As pointed out in the beginning the product has numerous settings, features and memories plus a number of inputs duplicated. This provides myriad ways to calibrate the product for different scan rates and input types, which is a good plus for the new user. What it does not provide are individual memories related to scan rate only, which would allow a single connection to the display and memory slots that automatically get loaded as you change the scan rate you are feeding it. For performance users, this should not create a problem provided you are feeding the display sources that meet video standards output at 1080i or 1080p and many performance users already have an external scaler to address those concerns.</p>

<p><br />
<B>Contrast Ratio</B><br />
This measurement is provided for the purpose of comparison only to other reviews of front projectors to illustrate true contrast ratios using a D65 calibrated color temperature using a 100IRE and 0IRE window pattern. With the dynamic iris feature, the Panasonic potentially comes with two measurements. When a projector provides that I measure the response with the dynamic iris turned off, this is a simple measurement of a 0IRE raster and 100IRE window after calibration. The probe is pointed towards the projector and moved towards it until .5fl has been obtained with a 0 IRE raster. Being able to obtain this contrast ratio on your screen will depend on how much of the light reflected off the screen gets reflected back to the screen by your room. This simple measurement does not account for the light in bright areas contaminating the black areas due to projector design and/or technology, which is called intrafield contrast ratio.</p>

<p>With the Dynamic Iris OFF, and using a calibrated D65 light output at 96 lamp hours, I obtained 367fl at 100IRE and .522fl for 0IRE yielding a contrast ratio of 703:1.</p>

<p>On the surface that is a good number. In reality it is somewhat poor because in a real system the perceived contrast is directly related to how black your blacks are. A CRT can easily have a lower contrast ratio but perceptually beat the pants off of a higher one because it can do inky jet blacks. In the world of performance front projection and micro display technology this is an average response.</p>

<p>Please refer to the article, <a href="/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php">HD Waveform 10 - Dynamic Iris and Gamma</a>, for an in depth look at this feature.</p>

<p><br />
<B>Light Output</B><br />
A light meter was not available to provide an accurate or meaningful number. Based on experience, this projector when calibrated is quite dim. 16:9 on my native 2.35 screen works out to 102" using a 1.4 gain. Using zoom to reduce the image to the smallest setting yielded 77" 16:9 and that was looking about right, but a bit more brightness would have been preferred. The problem here is the lamp only has about 75 hours on it and the light output will drop another 25% or more within the next 400 hours so. Being light challenged at this early stage in lamp life is not a good sign. Reverting back to manufacturer presets and some of the other setups, the screen became bright again with DYNAMIC providing plenty of punch as expected but visually that setting was error ridden which is also expected. 100" 16:9 is a commonly expected size in the home theater installation world so I would suggest a 2.0-3.0 gain screen in calibrated mode. Bear in mind screens in the 85-110" range typically vary from .8 to 1.3 based on the projector used for a committed room for a multitude of reasons; higher gains more often than not point to a unique situation. The out of box normal preset for color temperature and contrast did drive my 102" 1.4 gain to a pleasing level, which changes the perspective on lamp life and output.</p>

<p>Due to the extensive zoom range you have some variation in light output there as well. When set for the widest setting, the light path through the lens is spread out across the surface improving pass through efficiency. To take advantage of this will require the projector be placed quite close to the viewing screen. A disadvantage to that is that you take a hit in focus uniformity which means the edges will have a different focal point than the middle.</p>

<p><br />
<B>Lens Shift (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=87">Definition</a>)</B><br />
An installer will want to know where center is to reduce optical artifacts if you don't need the feature. Both lens shift controls provide a center detent position. There is play in these controls and they do not have a precision feel in your hand or on screen, but this is far better than the joystick approach Panasonic used in the past and with minimal tinkering you can get it very close if not right.</p>

<p><br />
<B>Noise</B><br />
This was an extremely quiet projector. Increasing <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=87">lamp power</a> will also increase fan speed but even that remained at an acceptable level. Throughout testing the lamp was set for normal.</p>

<p><br />
<B>Light Leakage / Stray Light</B><br />
This projector did not exhibit any light leakage.</p>

<p><br />
<B>Maintenance</B><br />
While all transmissive LCD projectors have filters to keep dust from getting into the optical cooling path, not all allow easy maintenance or replacement. The Panasonic does and recommends you do so every 100 hours. Due to this attribute of the system, it does not work well for the long term in some applications such as a bar or restaurant which could easily have tobacco smoke pumped through the optical cooling path. This may even be a worthy consideration in your home if you use tobacco or burn candles. An alternative to burning candles is to use a warmer if the scent is your intent. If your intent is for ambient lighting, purchase soot-free candles. Transmissive LCD is far from impervious to the environment in which it is used, and some have felt the sting of an expensive light engine to get the clean bright image back they once had. Remember my comment about LCD melt down? It is very important you keep this filter maintained! In the extreme, if the filter gets too much material in it, then the fan will begin to suck the finest debris through the filter, which can easily end up in your optics and appear on the screen as dots and smudges.</p>

<p>Lamp replacement will come. The great thing is the projector will tell you at 1800 hours that you ought to order one with a 30 second message when you turn it on. After 2000 hours the message remains until you make it go away by pressing any button. If mounted on the ceiling you will be pleased to hear the lamp cover is on the top (or in this case the bottom), which is the part you see when looking up! The customer menu allows you to reset the lamp timer.</p>

<p><br />
<B>Problems</B><br />
None</p>

<p><br />
<B>Subjective Viewing Results NORMAL</B><br />
Returning to King Kong, the calibration helped a lot in obtaining a response I am used to seeing. The color seemed overly saturated yet after hours profiling the product I just turned the color down a little. Blacks were slightly blue as expected from the RGB chart results. That same calibration reduced light output dramatically as discussed earlier. When using the PC, the iris had to be turned off lest it upset legibility. Even then there was some effort to read the screen. It was in this area of 1:1 pixel mapping that the Panasonic suffered. While I had little to complain about while gaming, using the internet was always a disappointment. Throughout it was noticeably flat in delivery rather than dynamic. Calibrated, it simply could not compete on any level with any of the calibrated DLPs in my stable. As for 1080p, it did look sharper and more detailed than my 720p DLP, yet compared to the 1080p DLP review projector it was incredibly soft, lacking in detail and dim. In the end I could not escape the loss in clarity, detail, dynamics and light output that my eye is accustomed to. While reducing screen size helped with the light output problem the projector could not escape its inherent limitations. Those same limitations had me turn on the dynamic iris feature to gain the perception of more light in exchange of other errors.</p>

<p>As a calibrator there is a point where you begin to realize that if science is not the products strength then you mix science and art for a better perceptual picture, which is exactly what I ended up doing for my large screen. I tried returning to the out-of-box setup conditions, which greatly improved things overall on a perceptual level, and is why insiders call that the "sales mode"! Knowing how wacky things were though I decided to keep the color temp settings and simply boost the contrast back up to 0 which definitely pointed things in the right direction without cyan whites. This did create a dramatic perceptual and real world improvement. Even in this mode my experience of better performing real world contrast ratios still left me disappointed with any scene that veered towards darkness. I was perceptually fooled only with bright images, yet even those retained a subtle flat look to them. Staying in this mode I watched The Devil Wears Prada on Blu-ray, Superman Returns on HD DVD and the first hour of West Side Story via HDNet and cable. All were entertaining. One strong characteristic came out though and that was a lack of color detail, creating a smeary poorly-focused look, very pronounced in saturated primary colors.</p>

<p><br />
<B>Subjective Viewing Results CINEMA 1</B><br />
As noted earlier, this mode and a few others engage a motorized function on the projector that dramatically reduces light output. While this may have the appearance and sound of yet another iris, I find nothing from the manual or website to substantiate that. Notably, nothing can be found about this noise or the visible result.</p>

<p>This was found on the website:<br />
Cinema 1 - Soft, smooth picture ideal for movies. Created under the supervision of David Bernstein, a leading Hollywood colorist.</p>

<p>In the brochure I found the following:<br />
<blockquote>Pure Color Filter Pro" for Professional-Level Color Reproduction. We equipped the PT-AE1000U with a specially developed optical filter that optimizes the light from the UHM projector lamp, helping to achieve deeper blacks while improving purity levels in the three primary colors (red, green and blue) that compose the image. It combines with our multilayered vacuum plating technology to create what we call the Pure Color Filter Pro. This advanced filter system improves color purity to such an extent that the color gamut is expanded nearly to the level specified in the Digital Cinema Initiatives (DCI)*4. To viewers at home, this means you see the kind of bright, vibrant colors and deep, rich blacks that make for great entertainment.</blockquote></p>

<p>The Color Filter Pro appears as the most logical candidate and seeing a picture of this optical device in the brochure it is also quite dark inferring a loss of light. Ultimately I could not test this setting at its full potential due to the enormous loss of light. That by itself will prevent it from looking comparable to any of the other displays I have in the house, regardless of technology or form. I decided to set up the product for a 77" screen and disregard the light problem. Images improved on many subtle levels, especially the dark scenes where instead of a blue black I had some black blacks and a better sense of color rendition. Based on what I was seeing, this mode appears to represent the product at its best. To take advantage of this mode will require a higher than normal gain screen with a small size and easily over 2.0 for larger screen sizes.</p>

<p><br />
<B>Day and Night Settings</B><br />
Over the last couple of years this has become a new feature in the control menu or on the remote for some displays that changes calibration settings based on ambient room light at the push of a button. Unfortunately many displays cannot do that and maintain relevant accuracy. The Panasonic is a surprising exception due to the Pure Color Filter Pro because not only is it a filter but it also acts like a manual iris reducing light output with the difference being you do not have a variable range. The difference in light output is significant enough that this projector could be implemented in a dark/medium room or medium/bright room. If this is an application you are seeking for this product I highly recommend you work with a professional to select the proper screen size and gain for optimal results. There are only a handful of products that can do dark/bright applications accurately; contact a professional.</p>

<p></p>

<p><B>Conclusion</B><br />
Based on features alone, the Panasonic excels at many things yet that bears little relation to final overall performance and that was the rub for this reviewer. I loved the motorized zoom and focus along with the vertical centering control for my zoom 2.35 application, which made changing back and forth nearly effortless. I am playing a PC game at the moment limited to 1024x768, and with the zoom I was able to fill out my screen while still retaining a native pixel map rather than using aspect controls, which destroy that very important performance attribute. I am going to miss that capability a lot. It gets most of the numbers right with the iris turned off and has good color management but it can't fix one native number and that is the inherent contrast ratio of transmissive LCD technology and the loss in light output. With video I preferred the errors of having the dynamic iris on. While it will pixel map there is clearly a loss in clarity at the single pixel level, another inherent trait of the technology and that artifact came out powerfully in my 1080p PC application. If you are looking for imaging science for the performance enthusiast, or reference imaging for mastering this not the right product for those applications. Having said that I must put CINEMA 1 in perspective; this is where imaging science is to be found, but you are going to have to use the oddball high gain screen and depending on your selection and viewing position that may cause sparklies and uniformity errors potentially taking a hit in detail which other products can fully deliver as well as good light output for common screens, so it does not make sense to this reviewer to use this projector for such an application. I would love to see Panasonic apply their engineering know-how to DLP technology providing a performance and entry level line of gear for front projection like they recently did for rear projection.</p>

<p>One potential confusing element in this story is the benefit of calibrating. It is clear that a full calibration correcting all aspects of the product is not going to be in your best interest unless you are shooting for the CINEMA 1 mode. That said I did find value by mixing some experience and art into the process. An ISF calibration does not stop at the display either covering your sources as well. In essence we are calibrating the complete video system from source to screen confirming you are getting the best response possible.</p>

<p><br />
<B>Putting It in Perspective</B><br />
If you want the better part of the 1080p wow factor, save some money and be able to put the projector where you want or need to then the PT-AE1000 should be towards the top of your list. The lens shift and zoom provides an ultra wide scope for placement and installation while maintaining image quality. The picture control feature set is inspiring. Most end users are likely to be first timers or casual viewers of the point-and-play variety. While perceptually great with video PC showed its limitations and only you can decide what is acceptable. It was severe enough that even casual viewers and first timers can easily pick up on it so this needs to be stated again.</p>

<p>If you are a performance enthusiast willing to go through the hassle of a correct installation and screen while keeping your viewing distance at 3.2 screen heights or more, why not consider imaging science 720p instead. Due to the rampage for 1080p those prices have dropped. A 1080p response is just one aspect of quality imaging and a close viewing distance is required for the payoff. You can still get the Samsung SPH710AE from $2,200-2,400 USD on line and that is a reference projector designed by one of the best known names in the business synonymous with performance; Joe Kane Productions! This is top notch performance on a budget. Bear in mind that for Samsung, this projector is considered an old model and out of stock. All that remains is what A stock (new factory sealed) is left in the distribution system or is being sold as B stock (refurbished).</p>

<p>If you are a 1080p performance enthusiast willing to go through the hassle of a correct installation and screen then why not spend $1-2K more and go with clearly better performance as well. Check the upcoming BenQ W10000 review or the Optoma HD81 which one of our authors recently purchased.</p>

<p>I have yet to review reflective LCD technology. Greg Rogers did a review on the Sony Pearl for Widescreen Review and there were some response similarities in the technical portion. Its reputation is also based on the dynamic iris technique.</p>

<p><br />
<B>Final Conclusion</B><br />
In the end I found this to be an entry level projector designed for an entry level installation. For most first time users and casual viewers this big screen 1080p experience is bound to have them grinning ear to ear and wondering why they waited. On that point, as a first timer or casual viewer, don't overlook another cost savings for the big screen experience in the form of the PT-AX100U at 720p, running anywhere from $1,300 - 2,200 USD. While not specifically reviewed, I suspect it will have a very similar response to its big 1080p brother.</p>

<p>Note: As this article went to publishing it was <a target="_blank" href="/forum/viewtopic.php?p=25340#25340">reported at HD Library</a> that Panasonic is providing a $1,000 USD rebate and one member found this projector for $2,749 USD. This is nearly half the price of projectors that can do better and taking all of its performance points into perspective this price level definitely hits the bang per buck category of entry level gear representing great value!<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>May  7, 2007 08:32 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 586
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
 			<h2>More on HDTV Projectors</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HDTV Projectors'
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
 				AND entry_id <> 586
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
