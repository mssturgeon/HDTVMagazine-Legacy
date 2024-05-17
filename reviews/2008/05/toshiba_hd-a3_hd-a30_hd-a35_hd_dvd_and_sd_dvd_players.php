<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 836";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 836 AND placement_is_primary = 1";
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
	<meta name="keywords" content="analog video, via hdmi, scan rates, multi channel, analog component, DVD, dvd, player, HDMI, hdmi, Toshiba, toshiba, video, audio, output, disc, performance, analog, players, response, PCM, content, those, pcm, bitstream" />
	<meta name="description" content="In case you hadn't heard, the HD format war is over. The Blu-ray camp struck a huge win just before CES when Warner Brothers announced they would be moving exclusively to Blu-ray by mid year, tipping the scales majorly in Blu-ray's favor. Then, in February 2008, Toshiba announced it would discontinue the development, manufacturing and marketing of HD DVD, officially ending the format war. They did, however, confirm that they would honor the warranty of all HD DVD players.

The Toshiba HD DVD players featured in this review were the latest (and as it turns out, last) generation to be released. While it may seem an empty effort to review them now since HD DVD has removed itself from the race, these third generation players from Toshiba have a performance response at crazy low prices to be reckoned with and deserve the full story." />
	<title>HDTV Magazine Reviews - Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Toshiba HD-A3, HD-A30, HD-A35 HD DVD and SD DVD players</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>May  1, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray Players">HD DVD & Blu-ray Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php&amp;phase=2&amp;title=Toshiba%20HD-A3%2C%20HD-A30%2C%20HD-A35%20HD%20DVD%20and%20SD%20DVD%20players&amp;bodytext=In%20case%20you%20hadn%27t%20heard%2C%20the%20HD%20format%20war%20is%20over.%20The%20Blu-ray%20camp%20struck%20a%20huge%20win%20just%20before%20CES%20when%20Warner%20Brothers%20announced%20they%20would%20be%20moving%20exclusively%20to%20Blu-ray%20by%20mid%20year%2C%20tipping%20the%20scales%20majorly%20in%20Blu-ray%27s%20favor.%20Then%2C%20in%20February%202008%2C%20Toshiba%20announced%20it%20would%20discontinue%20the%20development%2C%20manufacturing%20and%20marketing%20of%20HD%20DVD%2C%20officially%20ending%20the%20format%20war.%20They%20did%2C%20however%2C%20confirm%20that%20they%20would%20honor%20the%20warranty%20of%20all%20HD%20DVD%20players.%0A%0AThe%20Toshiba%20HD%20DVD%20players%20featured%20in%20this%20review%20were%20the%20latest%20%28and%20as%20it%20turns%20out%2C%20last%29%20generation%20to%20be%20released.%20While%20it%20may%20seem%20an%20empty%20effort%20to%20review%20them%20now%20since%20HD%20DVD%20has%20removed%20itself%20from%20the%20race%2C%20these%20third%20generation%20players%20from%20Toshiba%20have%20a%20performance%20response%20at%20crazy%20low%20prices%20to%20be%20reckoned%20with%20and%20deserve%20the%20full%20story.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img height="86" alt="HD-A35" src="http://www.hdtvmagazine.com/images/mt/ToshibaHDA3HDA30HDA35HDDVDandSDDVDplayer_8C8A/image.png" width="355" border="0">  <table class="greygrid"> <tbody> <tr> <td style="font-weight: bold; text-align: center" colspan="4">Pricing at publication</td></tr> <tr> <td class="greygrid"><b>&nbsp;</b></td> <td class="greygrid"><b>MSRP</b></td> <td class="greygrid"><b>Street</b></td> <td class="greygrid"><b>Amazon.com</b></td></tr> <tr> <td class="greygrid"><b>HD-A3</b></td> <td class="greygrid">$299.00 (2007)</td> <td class="greygrid"><a href="/equipment/model.php?man=Toshiba&amp;model=HDA3" target="_blank">$89.99</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000U62N1S?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000U62N1S" target="_blank">$132.50</a></td></tr> <tr> <td class="greygrid"><b>HD-A30</b></td> <td class="greygrid">$399.00 (2007)</td> <td class="greygrid"><a href="/equipment/model.php?man=Toshiba&amp;model=HDA30" target="_blank">$129.99</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000U6AHYS?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000U6AHYS" target="_blank">$354.99</a></td></tr> <tr> <td class="greygrid"><b>HD-A35</b></td> <td class="greygrid">$499.00 (2007)</td> <td class="greygrid"><a href="/equipment/model.php?man=Toshiba&amp;model=HDA35" target="_blank">$499.00</a></td> <td class="greygrid"><a href="http://www.amazon.com/gp/product/B000U6AHZW?ie=UTF8&amp;tag=hdtvmagazine-20&amp;linkCode=as2&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B000U6AHZW" target="_blank">$699.99</a></td></tr></tbody></table><br>HD-A3 Serial #: N/A<br>HD-A30 Serial #: N/A<br>HD-A35 Serial #: PL27Z00402<br>Warranty for all models: 1 year parts and labor<br><br><b>Summary: Videophile performance at mass market prices with one 1080p24 bug</b><br><br> <p>In case you hadn't heard, the HD format war is over. The Blu-ray camp struck a huge win just before CES when Warner Brothers announced they would be moving exclusively to Blu-ray by mid year, tipping the scales majorly in Blu-ray's favor. Then, in February 2008, Toshiba announced it would discontinue the development, manufacturing and marketing of HD DVD, officially ending the format war. They did, however, confirm that they would honor the warranty of all HD DVD players.  <p>The Toshiba HD DVD players featured in this review were the latest (and as it turns out, last) generation to be released. While it may seem an empty effort to review them now since HD DVD has removed itself from the race, these third generation players from Toshiba have a performance response at crazy low prices to be reckoned with and deserve the full story. For the movie buff that wants it all, HD DVD is still necessary since it could be some time before the movies that were released exclusively in that format are released again on Blu-ray. And for some titles, it may not be worth the effort re-releasing them on Blu-ray, leaving the HD DVD release as the only HD version available.  <p>The third generation players clearly show a cookie cutter style of manufacturing and marketing, offering three general retail models with differing features (HD-A3, HD-A30 and HD-A35) along with a warehouse retailer version (HD-D3). Looking at these players you would be hard pressed to see any difference from the front or back.  <p>The HD-A3 was purchased by a friend and tested at his house. The HD-A30 was purchased through a Best Buy outlet and then exchanged at a later date for the HD-A35 because, to the surprise of Best Buy staff and yours truly, only that model supports native bit stream for HD audio codecs. Those were tested here at the lab. In the end, I was unable to find any performance difference between these players when comparing similar capabilities. Indeed, the only difference appears to be the feature set.  <h2>Common Features for All Models </h2> <ul> <li>Component analog video up to 1080i for HD DVD and 480p for SD DVD  <li>HDMI digital video output up to 1080i  <li>HDMI supports Deep Color  <li>Composite analog video output  <li>Digital audio HDMI 1.3 PCM output supporting all sound track codecs  <li>SD Optical digital audio output  <li>Ethernet port for web enabled special features along with firmware upgrades  <li>HDMI-CEC CE-Link allows the player to interact with other CE-Link devices in your system for automated commands and functions </li></ul> <p>HD-A30 Adds  <ul> <li>HDMI digital video output up to 1080p, 60 and 24 frames  <li>HDMI 1080p 24 frame output for SD DVD; performance bug? (more on this later) </li></ul> <p>HD-A35 Adds  <ul> <li>Digital audio HDMI 1.3 bit stream output supporting all HD audio codecs </li></ul> <h2>Missing features you might have expected, all models </h2> <ul> <li>There is no multi-channel analog output for any model, only stereo. You can use the SD optical digital audio output but all HD audio codecs will be down converted to that standard. </li></ul> <h2>Opening the Boxes </h2> <p>The HD-A30 was well packed, yet cheap by comparison to previous models; not a big deal as much as an observation. I actually wondered if the product was a repack but looking at the factory seal and other things it didn't appear to be. The HD-A35 seemed packed better than the HD-A30, making me wonder about it being a repack. Those concerned about rack space and the larger cabinet styles of 1st generation product from either camp will rejoice over Toshiba's 2nd and 3rd generation players, which come in a smaller cabinet, cutting previous cabinet height by about half, to 2.25 inches. For the 3rd generation products the drawer is all the way to the right and everything else to the left and the same goes for the back panel of connections that are now on the right rather than the left. I did find it odd to have the front panel reversed from most player designs where the drawer is either on the left side or middle. It was a curiosity each time I had to access the drawer! In terms of fit and finish it does not have the high end impressionable look of the 1st generation players. Nope, this is a humble product at a humble price. If there was any sense of cheapness it was clearly found with the black remote included with all models up to the HD-A30 which had the look and feel of having been paired with a sub $100 cheap and inexpensive product for the masses. You won't feel like you are controlling your product in style. That said, it has one thing that fancy 1st generation remote didn't: functionality and ease of use. The cursor buttons work and you can read the labels, which trumps style any day! The remote for the HD-A35 is identical to what came with most of the 2nd generation players, being longer and skinnier and adding direct access simplistic TV controls, but overall the same fit and finish of the HD-A3 and HD-A30 remotes.  <h2>Out of Box Performance </h2> <p>Hooking up either player to a <a href="http://www.hdtvmagazine.com/reviews/2007/07/benq_w10000_1080p_dlp_front_projector.php">BenQ W10000 DLP Front projector</a>, I found it preset for 16:9 1080p. Going into the setup menu I switched the player to 1080p24 and ran the DVE HD DVD test material. Looking over at the receiver it showed a PCM multi-channel input with the HD-A30 and bit stream labels with the HD-A35 after changing some audio settings in the player menu. Everything seemed to look and sound great. On to objective testing...  <h2>On the Test Bench </h2> <p>This will be the first HD disc player review where all aspects of video performance can be bench tested using the Digital Video Essentials HD DVD/DVD combo test and calibration disc. What follows is objective testing for both SD DVD and HD DVD content via HDMI at 720p, 1080i and 1080p along with component analog video at 480p for SD DVD and component analog at 720p and 1080i for HD DVD. All video testing was performed on the HD-A30 in the lab with a follow up confirmation of the HD-A35. The HD-A3 was tested in the owner's home.  <p>Our current SD DVD reference player and benchmark is the fully reviewed <a href="http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php">OPPO DV-981HD</a>.  <p>The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection and copy protection since the means to see it would infer a means to steal it. At this time the <a href="http://www.hdtvmagazine.com/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">Panasonic PTAE-1000U</a> has been kept in the stable just for this purpose using the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video such as bursts, it is also the perfect tool for checking IRE levels and color decoding. This does come with the limitation of only being able to check YPbPr output, making me unable to verify the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal, as noted. All tests were performed using Digital Video Essentials test patterns as the source material.  <h2>Video Levels </h2> <p>With waveform monitoring, the Toshiba players output 0IRE and 100IRE at the correct 16/235 levels via HDMI for both disc formats at all HD scan rates.  <p>Via analog component, the Toshiba had a peak white output about 2 IRE above 100 IRE for SD DVD at 480p as well as HD DVD at 720p and 1080i. Considering the nature of video content, this is a marginal error that will only show up on occasion if perceived at all. For a digital display this may cause crushing of peak white especially if the display was calibrated without any headroom for exactly this type of condition. For analog CRT displays this will likely be negligible. Nonetheless, it is an error.  <h2>Color Decoding </h2> <p>With waveform monitoring the Toshiba players output correct color decoding at all HD scan rates with all formats and connections.  <p>Via HDMI or analog video, the Toshiba players had the usual amount of scaling artifacts observed for color bar patterns where two colors meet. Having artifacts in this area of response is unfortunately common. All the Toshiba players and the OPPO DV981HD generated about the same level of error over the same number pixels.  <h2>Horizontal Frequency Response Luminance </h2> <p>With HD DVD via HDMI, all scan rates reproduced a flawless response, as expected. Note that for 720p and 1080p testing the source content used was native to that scan rate as well as pixel mapped, a great feature of the DVE HD DVD disc providing a fair comparison for both HD scan rates.  <p>SD DVD is not pixel mapped. As noted, Waveform monitoring response was useless for this test. Visually the Toshiba passed the continuous frequency burst test at 1080p HDMI quite well for luminance, bettering the reference OPPO. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly ... and the Toshiba was no exception. This pattern always has banding as well. The best I can state on this is a high, medium or a low contrast response with high being the best and low being the worst. The Toshiba provided a high contrast response bettering the OPPO. 720p HDMI was similar in response but with a bit more banding which is to be expected when having less pixels to scale with.  <p>Analog component video had a similar response at 480p.  <h2>Vertical Frequency Response Luminance </h2> <p>With HD DVD via HDMI, all scan rates reproduced a flawless response, as expected. Note that for 720p and 1080p testing the source content used was native to that scan rate as well as pixel mapped.  <p>Via HDMI all scan rates reproduced a great response. Vertical frequency response was excellent in 1080p. Typically 720p scaling of SD DVD cannot figure out which dark and white stripes it should favor with white being predominant in the top or bottom burst and black predominant in the other. The HD-A30 sets a benchmark by getting this right for both areas at 720p.  <p>Analog component video had a great response at 480p.  <h2>Frequency Response Color </h2> <p>The Toshiba provided the best response so far providing a smooth even response across the screen for both red and blue channels for all formats at all scan rates and connections bettering the OPPO for SD DVD.  <p>Analog component video responded quite well at 480p.  <h2>CUE, Chroma Upsampling Error - SD DVD only </h2> <p>This causes a vertical breakup of color detail in the vertical plane, typically expressed in reds but can show up for other colors as well. It is related to the player using only one MPEG decoding method rather than both interlace and progressive and applying the correct version to the native source on the disc. The Toshiba failed this test but normal failure of this test is clear vertical tearing/combing of red. These artifacts appeared in a very subtle manner and videophiles are likely to pick up on it. CUE errors are much rarer these days, and with the error being subtle it is difficult to make a huge issue out of this.  <h2>Aspect Ratio Control </h2> <p>The Toshiba provides an auto 16:9/4:3 switching mode allowing the player to maintain correct aspect if the content is properly flagged with special features or 4:3 movies adding black side bars. The Toshiba provides another first for the 4:3 mode using no overscan at all; a welcomed surprise and yet again, better than the OPPO.  <p>For the DVD collector looking for great performance with all DVD mastering from 4:3 letter boxed sources to special features, the Toshiba has nothing to offer. This capability and level of performance still resides in the external scaling market.  <h2>SD DVD Scaling Analog Component Video </h2> <p>The Toshiba was tested at 480p HDMI feeding a 1080p DLP front projector with pixel mapped centered output, along with an adjusted viewing distance to compensate, using the DVE chapter 17 A/V Demonstration material.  <p>The Toshiba passed with flying colors providing the same common level of performance I would expect from most any 480p analog component video output.  <h2>SD DVD Scaling HDMI </h2> <p>The Toshiba was tested at 1080p60, 1080p24 and 720p HDMI feeding a 1080p DLP front projector with pixel mapped centered output using the DVE A/V Demonstration material.  <p>Wow! 1080p24 scaling with SD DVD? Don't get excited because the player does not use intelligent scaling and the raw source to actually do that. Instead this is an operational error that could be corrected via software. But until that happens, this is a problem area for both formats if using the 1080p24 output setting! SD DVD is encoded from the original 24 (film) or 30 (video) frame interlaced source for the format along with progressive flags that allows inexpensive dumb scaling within the player. The player then uses those flags to reconstruct the interlaced source as a progressive image as it is told. If the source is properly mastered with these progressive flags, you get excellent 480p 30 frame scaled material designed for the standard 60 hertz vertical refresh of all displays. So far, all players reviewed have used this dumb scaling system for SD DVD to generate quality 480p content and then scale it to 720p60, 1080i60 or 1080p60. The Toshiba works the same way except that it does not automatically switch back and forth between 1080p24 and 1080p60 to reflect the disc format you are watching unlike other players that have been reviewed; it stays in 1080p24 mode with SD DVD. This naturally generates intermittent artifacts with SD DVD directly related to image content, causing aliasing of edges and a vertical combing or tearing of color resolution. The only solution is to manually switch the output for the disc type you are viewing, which is inconvenient and bound to trip up the viewer at some point when they forget to check/switch. Adding insult to injury, if you happen to check after you booted an HD DVD disc and need to change scan rates, you get to wait and then find your place on the disc since this forces a reboot of the disc; SD DVD allows you to return to where you were. The lack of auto switching is a sad over sight for performance enthusiasts with 1080p24 capable displays trying to reduce the amount of boxes in our systems for convenience sake.  <p>Back to the correct 1080p60 setting for SD DVD...  <p>During testing, the Toshiba excelled, with the DVE test patterns outperforming our reference OPPO DV-981HD for the SD DVD format. With that kind of response, it was no surprise that the demonstration material was rendered just as well. A few more great discs were tested making it clear that with properly mastered and flagged content, the Toshiba is one fine SD DVD player, period! There is a catch though. If the material is not properly mastered and flagged, it has no intelligent scaling to correct source errors as was the case with the CUE test which it did technically fail. I also have a very nasty improperly mastered DVD for testing such things and the OPPO smoked the Toshiba on such content; thank goodness that is rare. With typical mastering errors, the OPPO faired a little better than the Toshiba and in all fairness all mass market manufacturers have depended on the flagged dumb scaling system for years because it works as well, if not arguably better than external scaling, provided the rest of the player processing was as accurate. To be clear this is a case of absolutes and picking nits. The difference will vary with your display, viewing environment and viewing distance. My setup requires the best or artifacts will be plain. With that said I would live with the Toshiba and the occasional poor DVD on this system rather than having to deal with yet another box and connection for those rare occurrences. For me, those rare titles can also be viewed on the far more forgiving casual system upstairs!  <h2>Scaling HD DVD via HDMI and Analog Video </h2> <p>For 1080i analog video testing I used the Panasonic PTAE1000 instead due to excellent deinterlacing of 1080i content. Switching back to the BenQ via HDMI and 1080p60 it still looked great although in some of the DVE material such as the CG graphics of plant life, there were visible aliasing problems. This was intermittent and directly related to the detail content of the material. No real surprise here since the content is 1080p24 and 1080p60. 1080i60 and 720p60 requires 2/3 pull-down processing of that content.  <p>Via HDMI and 1080p24 or 720p24 content the Toshiba delivered video perfection within the capability of the format with all test materials.  <h2>Audio Performance </h2> <p>The application of HD audio to either of the HD disc formats is quite complex, with potential land mines along the way. This is not an article on that topic, and for complete info I refer you to <a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php" target="_blank">Multi-channel Audio for HD</a>. <p>With the HD-A35 providing full bitstream support of all HD audio codecs, all you need is a compatible A/V receiver of your preference. For those with transitional PCM multi-channel HDMI supported A/V receivers, you are set to go with HD audio converted to multi-channel PCM and SD DVD outputting the native bitstream. Both methods require you use a digital video connection such as HDMI or DVI with HDCP. To be clear, the HD-A3 and HD-A30 DO NOT support bit stream from HD DVD, only PCM, still a huge step forward from using analog multi channel output and input connections.  <p>The HD-A3 and HD-A30 convert the native codec of either format to PCM multichannel when using the HDMI output for both audio and video. You will need to enter the setup menu and change the settings for SD DVD to PCM or it will be only 2-channel. If you want the native codec from SD DVD you will have to connect the SD digital output to your receiver and switch to that input for the audio along with the settings for SD DVD to bitstream.  <h2>Bitstream versus PCM</h2> <p>The HD-A35 provided my first opportunity to directly compare the native bitstream to converted PCM. While PCM decoding should be identical in theory, the reality is upon entering your A/V receiver it will be sliced and diced yet again so it can be applied to your room correction and speaker setup which is part and parcel of any HT sound system. One of the annoying attributes of analog multi-channel inputs was the fact that you could not typically perform those functions at the receiver and the player lacked the in depth adjustments as a substitute. Those receivers that could perform those functions had to convert the analog back into digital for the slicing and dicing adding another process to degrade the sound. By being able to pass on the native bitstream from the disc to the receiver, some of these processing steps get bypassed providing the potential for superior performance. Another possibility is that the chip sets and codecs used by the receiver are superior to those used in the player. Based on testing, one of the two (or a combination of both) is what my ears experienced. The difference was not dramatic but it did exist. The best description is slightly more of everything as if a slight veil had been removed between my ears and the speakers. Like SD DVD, if you are looking for the best in sound, native bitstream decoding by your A/V receiver is the future!  <p>Special features on the disc may add another layer of complexity for bitstream users depending on their application. Features such as directors comments or the new streaming of special features as a PIP function while watching the movie requires one or more additional tracks be added to the original soundtrack of the movie or allow switching back and forth, better known as advanced audio mix. Bitstream applications do not support that capability. To avoid end user confusion and possible complaints when the special features of a movie are missing their audio tracks, some discs may be flagged preventing your ability to ever process the original bitstream. Disc authors themselves may desire this to insure that you will fully benefit from the experience they have created (none of the HD DVDs used in this review had that flag). Under these circumstances, the player will only support such features as PCM or SD bit stream. When the disc is flagged this is automated and your HDMI bitstream will be switched to PCM so there is nothing for you to do. When optional, you will have to switch the player output to PCM for HDMI or use the SD digital connection for these special features. This application was not tested. <h2>Mixing Analog Component Video with HDMI Audio </h2> <p>Only those with a display that does not support HDCP HDMI or DVI need to take note of this. For those upgrading a legacy home theater system using analog video connections and starting on the audio side with HDMI audio you are going to have some problems just like other HD disc players. If you intend to use the HDMI connection for audio and HD DVD, you will be pleased to know that you will get full HD audio support via bitstream or multi-channel. Unfortunately with SD DVD you will get sound but will be left out in the cold with no image at all. The fix for that is to use the optical SD digital audio output and setup another input on your receiver for that digital input. That should release the HDMI handshake from turning on the analog video again. As I was checking out how this would work for you I also found another operational bug; to get the bitstream from SD DVD I had to go into the setup menu and change the SPDIF output to bitstream, yet when I switched back to HD DVD the receiver would only indicate PCM rather than PCM 96khz requiring me to go back into the setup menu and change the SPDIF back to PCM to get PCM 96khz on the receiver. While SD DVD allows you to stop, make changes and return back to where you were HD DVD always forces a reboot if you press the stop key. The following is a frustrated general statement based on this and other reviews. This is yet another firmware or design flaw that could be overcome by allowing the product to automatically switch to the proper settings depending on the end users application. There is no reason these players cannot output HD audio via HDMI using bitstream or PCM with an HD disc at 1080i or 720p analog video and switch to SD bitstream or PCM at 480p analog video with SD DVD allowing those in transition to get the maximum benefits from both formats on the same player without additional setup effort, A/V switching or another player.  <p>Putting this in perspective, those performance enthusiasts with legacy multiscan displays supporting native 480p probably have a good legacy 480p DVD player to go along with it. You could just keep using that for DVD if an additional box in the rack is of no concern. I get hit with folks wanting to upgrade to an upconverting player often and if you have a native 480p scan rate there is little to gain by upconverting to your HD scan rate and it could be argued you are creating more artifacts rather than less.  <h2>Ethernet Port </h2> <p>For most users you need to enter the setup menu and turn DHCP on and within that menu set DNS for auto. There are other settings to insure network compatibility. This port provides support for extra features using web based content along with firmware upgrades. Checking firmware, the player reported I had 1.0 and that 1.1 was available. The player failed on the first attempt to upgrade showing an error code on screen which locked up the player requiring an AC reboot, unplugging the AC cord. This qualifies as yet another performance bug because for most installations the product will likely end up buried with other equipment making access to the AC cord very inconvenient. The second attempt ended in success along with the fact that it was updated to 1.3, the current version as posted by Toshiba on their website and <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=8722">here at HD Library</a>.  <h2>Problems </h2> <p>During testing one frustrating operational element reared its head over and over; While SD DVD allows you to stop, make changes and return back to where you were HD DVD always forces a reboot if you press the stop key. While mentioned already, it bears mentioning again.  <p>Like the HD-A1, I continued to have an intermittent HDMI problem where the picture turned into a pixilated all red version of what it should be, related to the continual HDMI handshaking that occurs with either HD format as it goes from one bit of disc content to another on it's way to the main feature. The good news is this player finally supports hot plug and play of HDMI, unlike the HD-A1, and the fix was as simple as changing to another input and back to HDMI. For the HD-A1 I had to stop the disc and as noted that forces a reboot of the disc along with a test of my patience for that player. I don't know if this is the player or my system and cables. This problem has not occurred with any other HDMI source so naturally I am inclined to blame it on the player/Toshiba. That said, a subsequent change in the lengthy HDMI cable to the projector appears to have put a stop to this.  <p>The HD-A30 skipped a beat in one of those crucial climax scenes during the end of Blood Diamond, a Netflix rental. Fortunately it did not destroy the entertainment but we were all cringing for a moment in anticipation of a complete lock up that never happened.  <p>The HD-A30 and HD-A35 had problems with a Netflix rental of The Shooter during the last five minutes of the disc, which also happened to be one of the climaxes. To add insult to injury it locked up the player requiring removal of AC power to make it operational and for those with the product buried in a rack, a painful proposition! After numerous lengthy attempts I gave up and put the disc in an Xbox 360 setup with HD DVD drive and it played flawlessly. Physically the disc had scratches but in my experience I have seen discs in far worse condition.  <p>During Serenity, a virgin HD DVD disc purchase, the HD-A35 direct bitstream HD audio simply stopped from one scene to the next and if it weren't for the fact that I had seen the movie before we might have watched for minutes as the lack of sound appeared to flow with the scene. Rewinding back to the error brought the sound back to life. Towards the end of the movie the picture started breaking up into blocks, the sound went haywire for a moment and when everything returned to normal there was a huge lip sync error. Simply pressing pause and then play cured the problem.  <p>I can't tell you why I seem to be plagued with these kinds of HD DVD problems, but integrity requires I report this. That same integrity requires I remind you that there are plenty of others who have not had a problem but there has been no research to clear the air on whether this is a format problem or a hardware problem. Consider the Xbox 360 HD DVD drive experience as one hardware example that saved the day for The Shooter.  <h2>Service </h2> <p>Like nearly all DVD players these days, there is no field support for service and repair. Contact Toshiba for the nearest repair depot for repair or exchange.  <h2>Conclusion </h2> <p>For 1.5 years the performance community has been waiting on a statement product for the HD DVD format and the Toshiba HD-A35 delivers that reference point for the best HD DVD can be providing native 1080p24 for the video and native HD audio codec bitstreams for your preferred compatible A/V receiver!  <p>SD DVD scaling was exemplary and amongst the finest with good material. While it may not be the best with all content on DVD for the most part the main feature, the movie, is covered. If you want the best scaling for all content then an external scaler with an SDI equipped DVD player remains your only choice although an expensive one.  <p>Unfortunately, there is a bug in the system. If you are running a 1080p24 display then the best response will only come from manually changing the output to the appropriate scan rate for your content. Let your ability to always remember to check be your guide.  <p>It was evident from testing that as you go down from the HD-A35 to the other models there is no penalty in delivered performance, only in features. For analog component video applications the player receives high marks in scaling for both HD DVD at 1080i and SD DVD at 480p! Remember the video level error though for peak white, especially if you have a digital display.  <h2>Putting It in Perspective </h2> <p>The Toshiba provides reference quality for both SD and HD DVD formats. In that regard all models were a winner! Unfortunately for the technology challenged performance mass market and performance HT installers, the 1080p24 bug of the HD-A35 and HD-A30 with SD DVD is frustrating to no end. These clients are looking for auto pilot; not performance baby sitting nor multiple keystrokes on a remote in a menu they likely care less to understand. For most of these installations I can't see anything else to do but take a hit with one of the formats; artifacts with HD DVD at 1080p60 or artifacts with SD DVD at 1080p24. For the hands on user it is bound to become a nagging frustration to remember to switch scan rates all the time. Upgrading the firmware to 1.3 changed nothing on this front. The only good news is this has to be a simple firmware fix. The bad news is based on past history, if it even happens; it will likely happen in the long term rather than short. When the 2nd generation players were released the performance community wailed over the <a href="http://www.hdtvmagazine.com/forum/viewtopic.php?t=6873">lack of native 1080p24 support</a> for the HD DVD format and some folks at Toshiba said they would deliver while others refused to answer. Many retailers made promises to their customers! The fact is it took Toshiba about a year to provide that. With the demise of the format it is questionable that any further effort would be expended over this. Toshiba, please get this fixed!  <p>Compared to other players I have reviewed, it is on top purely for delivering reference performance with both formats. I don't really need an external scaler or separate DVD player for top notch performance and that is a first so far.  <p>If you are looking for an HD DVD player, any of the models provide a performance to cost ratio that currently is simply out of this world; choose the feature set you want and enjoy! On top of that, HD DVD discs are going to become very cheap in the coming months and some current owners see nothing but future opportunity.  <p>If you are looking for an upconverting player for SD DVD that does 720p, 1080i or 1080p the HD-A30 at $199 MSRP is 100% competitive as well as a huge videophile bargain considering the reference response! If you don't need or want 1080p support then the HD-A3 is $149 MSRP and would do quite well with a native 720p display. For an SD DVD upconverting performance comparison our reference OPPO DV-981HD is $249!  <p>If you were looking for a long term HD disc player and couldn't decide which one that contest is over. Skip HD DVD and buy a Blu-ray player.  <p>Four recent articles and feedback on the format war:  <ul> <li><a href="http://www.hdtvmagazine.com/articles/2007/11/is_it_my_choice.php">Is It My Choice, or Is It Yours?</a>  <li><a href="http://www.hdtvmagazine.com/articles/2007/11/hd_dvd_or_blu-r.php">HD DVD or Blu-ray: My Choice is...?</a>  <li><a href="http://www.hdtvmagazine.com/articles/2007/11/which_is_more_consumer_friendly_hd_dvd_or_blu-ray.php">Which is More Consumer Friendly: HD DVD or Blu-ray?</a>  <li><a href="http://www.hdtvmagazine.com/news/2008/01/hd_dvd_rallies_consumer_audience_in_2007_driving_nearly_one_million_dedicated_player_sales_in_north_america.php">HD DVD Rallies Consumer Audience in 2007 Driving Nearly One Million Dedicated Player Sales in North America</a> </li></ul> <h2>Final Conclusion </h2> <p>The Toshiba HD DVD players are a great spring board into HD disc for multiscan 480p/1080i CRT legacy displays. While there are other displays with more resolving power, those old CRT products beat them hands down in other areas and Toshiba has a great player to add new HD life to your experience.  <p>For those with new displays supporting HDMI, any of the players provide quality audio and video performance and for those with 1080p24 capable displays the HD-A30 or HD-A35 provide a reference video performance envelope. The HD-A35 adds the HD audio native bit stream finale for the best HD DVD can be putting you directly in touch with the studio!</p></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>May  1, 2008 09:55 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 836
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
 			<h2>More on HD DVD & Blu-ray Players</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray Players'
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
 				AND entry_id <> 836
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2008/05/toshiba_hd-a3_hd-a30_hd-a35_hd_dvd_and_sd_dvd_players.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
