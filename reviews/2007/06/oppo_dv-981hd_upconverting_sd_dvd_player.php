<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 613";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 613 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dvd audio, frequency response, oppo players, sacd dvd, dvd player, DVD, dvd, oppo, OPPO, audio, output, video, players, disc, player, multichannel, Audio, response, analog, SACD, sacd, HDMI, hdmi, receiver, performance" />
	<meta name="description" content="In the summer of 2005 I came across a well regarded DVI upconverting DVD player from a new kid on the block by the name of OPPO and decided to give it a whirl. This culminated in August of 2005 into a Review in Progress report for the OPDV-971HD in which I gave the product a very high rating. I have been recommending OPPO ever since. 

Since then, OPPO has released another model, the DV-970HD. The DV-970HD added HDMI output, SACD and DVD Audio, analog audio with the ability to turn the video stages off for better audio reproduction, and a unique 480i output directly from the disc for the scaling videophile." />
	<title>HDTV Magazine Reviews - OPPO DV-981HD Upconverting SD DVD Player</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/oppo_dv-981hd_upconverting_sd_dvd_player';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('OPPO DV-981HD Upconverting SD DVD Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">OPPO DV-981HD Upconverting SD DVD Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>June 14, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Upconverting DVD Players">Upconverting DVD Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php&amp;phase=2&amp;title=OPPO%20DV-981HD%20Upconverting%20SD%20DVD%20Player&amp;bodytext=In%20the%20summer%20of%202005%20I%20came%20across%20a%20well%20regarded%20DVI%20upconverting%20DVD%20player%20from%20a%20new%20kid%20on%20the%20block%20by%20the%20name%20of%20OPPO%20and%20decided%20to%20give%20it%20a%20whirl.%20This%20culminated%20in%20August%20of%202005%20into%20a%20Review%20in%20Progress%20report%20for%20the%20OPDV-971HD%20in%20which%20I%20gave%20the%20product%20a%20very%20high%20rating.%20I%20have%20been%20recommending%20OPPO%20ever%20since.%20%0A%0ASince%20then%2C%20OPPO%20has%20released%20another%20model%2C%20the%20DV-970HD.%20The%20DV-970HD%20added%20HDMI%20output%2C%20SACD%20and%20DVD%20Audio%2C%20analog%20audio%20with%20the%20ability%20to%20turn%20the%20video%20stages%20off%20for%20better%20audio%20reproduction%2C%20and%20a%20unique%20480i%20output%20directly%20from%20the%20disc%20for%20the%20scaling%20videophile.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/?partner=826"><img src="/images/products/oppo-dv-981hd.jpg" alt="OPPO DV-981HD" /></a><br /></p>

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.oppodigital.com/?partner=826">$229.00</a></td>
<td class="greygrid"><a target="_blank" href="/equipment/model.php?man=OPPO%20Digital&model=DV981HD">$229.00</a></td>
<td class="greygrid"><a target="_blank" href=" http://www.amazon.com/gp/product/B000LU8A7E?ie=UTF8&tag=hdtvmagazine-20&linkCode=as2&camp=1789&creative=9325&creativeASIN=B000LU8A7E">$229.99</a></td></tr>
</table>
<br />
Serial #VD0644601484<br />
Warranty: 1 year parts and labor<br />
<br />
<B>Summary: OPPO has an SD DVD upconverting player for both videophiles and casual viewers at the right price</B><br />
<br />
In the summer of 2005 I came across a well regarded DVI upconverting DVD player from a new kid on the block by the name of OPPO and decided to give it a whirl. This culminated in August of 2005 into a <a target="_blank" href=" /forum/viewtopic.php?t=5548">Review in Progress report for the OPDV-971HD</a> in which I gave the product a very high rating. I have been recommending OPPO ever since. 

<p>Since then, OPPO has released another model, the DV-970HD. The DV-970HD added HDMI output, SACD and DVD Audio, analog audio with the ability to turn the video stages off for better audio reproduction, and a unique 480i output directly from the disc for the scaling videophile.</p>

<p>The focus of this review is the recently released DV-981HD. This model provides 1080p output and multichannel PCM audio via HDMI for SACD and DVD Audio. Excluded from this model is the native 480i output via HDMI external scaling. This also happens to be the only model that comes in black, the preferred color for home theater systems.</p>

<p>As expected, neither the OPDV-971HD nor the DV-970HD will allow upconversion via the analog component video outputs. This connection is limited to 480p. The DV-981HD does not even provide component video output due to the predominance of HDMI and DVI connections on most modern displays.</p>

<p><br />
<B>Common Features</B><br />
OPPO has done a great job outlining these features on their website. Please refer to the links below for the features and specifications of each model. Also available on these pages are firmware upgrades and owners manuals.</p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/opdv971h.html">OPDV-971HD</a> (1st generation, DVI output)</p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/dv970hd/dv970hd.html">DV-970HD</a> (2nd generation, HDMI output)</p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/dv981hd/dv981hd_features.html">DV-981HD</a> (3rd generation, HDMI output)</p>

<p><br />
<B>Not-So-Common Features</B><br />
<ul><li>DCDi by Faroudja video processing technology</li><li>User adjustable video controls: Sharpness, Contrast, Brightness, and Color Saturation</li><li>PAL/NTSC disc and TV compatible with automatic or manual system conversion</li><li>No Analog Component Video on the DV-981HD model; requires HDMI or DVI digital video input. It does provide s-video and composite video connections.</li><li>High-resolution multi-channel digital audio output through HDMI supporting CD, DVD-Audio, SACD, Dolby Digital and DTS sound tracks.</li><li>Optical and Coaxial digital audio output</li></ul></p>

<p><br />
<B>Opening the Box</B><br />
OPPO has changed their packaging somewhat in an effort to lend itself better to internet sales and individual shipping.  Nonetheless, I was left with the same feeling of unexpected quality at this price point. The unit still comes in a nice bag, well packaged and includes all you need for an easy start. OPPO has also changed their remote somewhat from the original OPDV-971HD, but do not expect much here. While certainly not haling from the land of cheese, it is not back lit and button layout veers more towards a tabled layout with button shape similarity, adding to the confusion. While it has glow in the dark keys, that won't help much once the glow has extinguished itself in your darkened room. I don't place too much emphasis on remotes though as most folks use a system remote for everyday use.</p>

<p><br />
<B>Out of Box Performance</B><br />
Hooking up the player to a BenQ W10000, I found it preset for 16:9. I adjusted the output for 1080p and ran the DVE test material. Looking over at the receiver it said stereo. Going into the setup menu I found down mix set for stereo and changing that to 5.1 took care of that. To get linear PCM you will have to change a number of settings, all documented on page 18 of the <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://oppodigital.com/dv981hd/download/dv981hd_manual.pdf">owners manual</a>. After about an hour of looking at DVDs, nothing appeared to be wrong, so on to objective testing.</p>

<p><br />
<B>On the Test Bench </B><br />
The very ability to inspect and view an HDMI video source goes directly against the copyright capability of the connection and copy protection since the means to see it would infer a means to steal it. At this time, the Panasonic PTAE-1000U (<a target="_blank" href="/reviews/2007/05/panasonic_pt-ae1000u_lcd_front_projector.php">recently reviewed</a>) has been kept in the lab just for this purpose because it has the Wave Form Monitor feature. While the Wave Form Monitor does suffer when looking at high frequency response video ,such as bursts, it is also the perfect tool for checking IRE levels and color decoding. It is limited to only being able to check YPbPr output. It is unable to verify the switching to RGB output that would be required for a DVI input. Some of the results are based on visual calibration checks as well as signal and is noted. All tests were performed using Digital Video Essentials as the source material.</p>

<p><br />
<B>Video Levels</B><br />
Whether by visual calibration or waveform monitoring, all of the OPPO players output 0IRE and 100IRE at the correct 16/235 levels.</p>

<p><br />
<B>Below Black</B><br />
Video content is 0-100IRE. The ability to pass a below black signal (lower than 0 IRE) is required to properly use the video standard pluge pattern for setting or confirming the black level on the display. The OPPO passed this test</p>

<p><br />
<B>Color Decoding</B><br />
Whether by visual calibration or waveform monitoring, all of the OPPO players output correct color decoding at HD scan rates, 720p, 1080i, 1080p.</p>

<p><br />
<B>Horizontal Frequency Response Luminance</B><br />
As noted, Waveform Monitoring response was useless for this test. Visually the players pass the continuous frequency burst test quite well for luminance. For the low frequency pattern there is some banding for the highest frequency burst. Moving on to the high frequency pattern, recall that I have yet to see any player or scaler/player combo pass this pattern correctly and the OPPO players are no exception. This pattern always has banding so the best I can state on this is high, medium or a low contrast response with high being the best and low being the worst. For the OPPO players a medium contrast response was the norm; a typical response compared to others.</p>

<p><br />
<B>Vertical Frequency Response Luminance</B><br />
Vertical frequency response was excellent in 1080p. 1080i showed banding and a drop in video level response. With 720p the player could not figure out which dark and white stripes it should favor with white being predominant in the top burst and black predominant in the bottom burst.</p>

<p><br />
<B>Frequency Response Color</B><br />
All OPPO players showed some banding, which is quite normal. The contrast levels remained fairly equal from low to high. I have seen slightly better definition though using scaler/player combos.</p>

<p><br />
<b>CUE, Chroma Upsampling Error</b><br />
This causes a vertical breakup of color detail in the vertical plane, typically expressed in reds, but can show up for other colors and is related to the player using only one MPEG decoding method rather than both interlace and progressive, and applying the correct version to the native source on the disc. All OPPO players pass this test.</p>

<p><br />
<B>Aspect Ratio Control</B><br />
The DV-981HD provides an auto 16:9/4:3 switching mode so the player maintains correct aspect. With special features or 4:3 movies, black side bars are used. OPPO calls this 16:9 Wide/Auto and is found in the setup menu.</p>

<p>For the DVD collector looking for great performance, the OPPO does have an Achilles Heal when it comes to letterboxed sources. While a combination of the Wide/Auto setting along with the zoom feature will properly fill out your screen, the image you get is, simply put, terrible. This is due to a bonanza of aliasing errors and others. One solution is to use the internal scaler of your display and live with those artifacts; the very reason you would buy the OPPO in the first place. Putting in perspective, most players don't have it and these days new 4:3 letterboxed titles destined for the US are basically history. These were either a byproduct of films that were mastered for the old laser disc widescreen format or are from content providers using the original master instead of producing a new one to save a buck on the DVD version. Taking all that into account, this is a very rare problem for the movie itself. </p>

<p>On the other hand if you are into special features or foreign origin DVDs, much of that content for SD DVD is still letterboxed. HD disc, Blu-ray and HD DVD do a great job at excluding, scaling or cropping this material for your 16:9 screen or display in HD so no adjustment is required. If you are a passionate videophile then your best bet is to use an external scaler/DVD player combo. The DV-970HD is designed for that application but was not tested for this review.</p>

<p><br />
<B>Scaling</B><br />
I tested the OPPO at 1080p, 1080i and 720p feeding a 1080p DLP front projector that supports 1:1 pixel mapping with other scan rates. As expected the edges were soft, which is a byproduct of the scaling process for nearly any manufacturer. Color bar patterns showed the typical dark edging where the different colors met.</p>

<p>Moving on to test images from DVE at 1080p I was greeted with a very good response. The unit is not perfect, but perfection for SD comes at a very high price. The typical line interpolation errors and aliasing showed up although far less at 1080p. At 720p the process loses 44% of the pixels that 1080p provides and naturally has more of these errors. The most interesting observation occurred with edge definition and 1080p. Comparing 720p to 1080p it became quite apparent that having 44% more pixels greatly contributes to harder and more distinct edges since the change from peak black to peak white takes place over a smaller area by comparison. This was expected technically, but it was nice to see visual verification of this theory. An increase to a 2k x 4k imaging chip would double the pixel count yet again easily providing SD DVD edges nearly as distinct as HD! Putting this in perspective, you would need a viewing distance less than 3 screen heights to perceive this benefit and that is typically reserved for front projection and large screens.</p>

<p>The next disc up was Star Wars Episode II, a disc I have tested to no end over the last year with numerous products. I recently stepped into 1080p land and while I found HD DVD and Blu-ray a thrilling experience, I happen to run this movie through the Toshiba HD-A1 output at 1080i and was not pleased. We watched 2 more DVD movies at that time and I gave up on my 10 foot wide 1080p for SD DVD and watched those on the upstairs 720p 50" DLP system instead. Then came this player. Using the OPPO DV-981HD at 1080p output instead provided the cure! The artifacts were basically gone and I found myself involved and sucked right in very quickly! That is always a hallmark of a videophile accomplishment.</p>

<p><br />
<b>Additional Video Features</b><br />
<ul><li>True Life Enhancement<br />
This is a complex edge enhancement process. Whether or not to use this feature is debatable. In testing I found it to be quite subtle if non existent at times. It can add a bit more dynamic look with the right video content. In the end I preferred it off for accuracy.<br />
 </li><li>Motion adaptive Noise Reduction<br />
Typically turn this off. For the most part, it will only suppress film grain and that is decided by the mastering house and producers. That is the artistic part of film making. If you don't like film grain use this feature.<br />
</li><li>Cross Color Suppression<br />
This feature relates to composite video sources used for mastering to DVD. The site explains this quite well. This applies to some special features only and the rare DVD in which the main content was mastered that way (I have one). Most content these days is captured and stored as YPbPr eliminating the cause of this artifact.</li></ul></p>

<p><br />
<B>Audio Performance</B><br />
Video testing was easy and straight forward. Audio testing created a fog of unknowns and lots of research related to specifications of products stating what they can or cannot do. Both OPPO players claim SACD and DVD Audio compatibility, which by itself only infers that those formats will work, not that they will be fully implemented. The Pioneer VSX-81TXV receiver I used for testing comes with its own fog, never stating what multichannel PCM streams it will support. This creates a dilemma for those passionate about performance since these products clearly worked together yet failed to state or output the correct version for 24/192 DVD Audio or arguably SACD. OPPO's specs for all three players state the analog audio frequency response to be 20hz-20khz, clearly a CD spec and not high enough for HD audio sources. For the OPDV-971HD, which does not support HD Audio, they not only state it uses a 24/192 DA converter but the chip used as well. And for the DV-970HD, they state Optimized analog audio circuitry for great audio quality and Unique "Audio Only" mode with video processing turned off for perfect acoustic fidelity, which infers it has at least 24/96 DA converters if not 24/192 which does not agree with the frequency response spec. Back in the day of newly released DVD Audio, the players audio section had frequency response specifications for all three rates, 16/44, 24/96 and 24/192.</p>

<p>I asked OPPO about all this and the official response was that both players have 24/192 DA converters for analog audio and it will output a digital 24/192 2 channel PCM stream with 24/192 DVD Audio discs. I would like to thank HDTV Magazine Tips List member Brent Wagner for verifying that he was able to get 192khz to display on his Pioneer VSX-84TXSi receiver using HDMI and a 24/192 encoded disc.</p>

<p>All in all analog performance was average at best and for the average multichannel system it will suffice. Considering the price of either player, audiophile analog conversion of SACD and DVD Audio sources is not a reasonable expectation and none of the OPPO products provide any ground breaking surprise in that department. If you are an audiophile seeking audiophile stereo or multichannel analog performance outputs you will have to look elsewhere. Many will find the analog output satisfying and an improvement when comparing CD sources to SACD or DVD Audio on either OPPO player. That said there was little comparison to my reference Sony SCD777ES for SACD or modified JVC XLV720 for DVD Audio. The lack of transient response, clarity and neutral sonic signature was evident. It could be argued that an audiophile performance CD player is comparable in many ways to HD audio via the OPPO. In the end both players provide a good entry level audio signature that is to be credited for a smooth response while not doing anything grossly wrong or irritating causing listener fatigue. On the other hand both players provide multichannel PCM streams via HDMI for a capable receiver and this is where some form of audiophile nirvana is to be found.</p>

<p>I tested the OPPO PCM stream using a Pioneer VSX-81TXV A/V receiver which provides 24/192 DA conversion for the outputs. In this mode I was able to duplicate all DVD Audio formats out to 24/96 yet when a 24/192 disc was put in the player the receiver stated 96khz on the front panel. When an SACD is played on the OPPO the Pioneer indicates 88.2khz. In this area things became far dicier for me as a reviewer. My 2 channel system is composed of custom and modified products designed and setup for the ultimate expression of a neutral audio signature. My multichannel setup is clearly a compromise by comparison using off the shelf stock products with a speaker arrangement that is not optimized for multichannel applications. Accordingly, I need to keep all of this in perspective in my comments.</p>

<p>The best place to start in tearing this down is the source, player and disc. The vast majority of DVD Audio is 24/96 multichannel encoded so based on that you potentially have a clear shot from the disc multichannel decoding to the DA converters on your receiver, provided the OPPO is decoding the bitstream off the disc properly. To acquire this in your system requires you setup the OPPO for the PCM stream and the OPPO audio menu for multichannel speaker setup, setting your speakers as small with subwoofer on so the decoding remains native to the source. It was in this mode with 24/96 sources that the two HD audio capable OPPO players shined the best without question. So much so that I am strongly considering revamping my multichannel application for better performance to provide a more in depth sonic experience! Beyond that things begin to get dicey due to conversion of other formats like SACD and 24/192 PCM sources. With 24/192 I can only state that like 24/96 if your receiver supports it then it will sound as good as the receiver. Whether in multichannel or stereo mode, SACD was lacking in clarity. Not only was it being down converted* to 88.2khz, but also being converted from DSD to PCM. That said there were other aspects of SACD multichannel playback compared to top notch stereo that provided pause in my evaluation. To go in depth would extend the length of this review dramatically in trying to provide perspective on those technical issues. Ultimately, getting the full potential of SACD performance is an audiophile concern and I suggest an audiophile stand alone player. I know that is not an easy or inexpensive product to find. It is unfortunate, but in the end maintaining a pure unconverted signal for SACD from source to decoded analog output whether that be stereo or multichannel analog or digital is a huge challenge for the end user on a budget.</p>

<p>This also drew comparisons between a decoded multichannel PCM stream and the raw bitstream off the disc feeding the receiver for DVD movie soundtracks. Splitting hairs, I found I preferred the bitstream for an edge in overall clarity. This brings up the current debate concerning HDMI 1.3 support for HD Audio bitstreams which would provide a straight shot from the disc to your receiver. While a digital PCM multichannel stream is typically considered superior to an analog multichannel input, there remains a process of conversion that will take place as the channels are sliced, diced and converted to the settings you have designed for your room in the receiver. The only to way to get a straight shot through the receiver is to turn all such processing off as if it was a direct analog feed and output directly to the amps. But that would defeat the purpose and improvements gleaned by room correction. With a bitstream, the decoding and processing all take place within the same domain reducing conversion steps. The flip side of this argument is digital, but any videophile with external scaling also understands conversion of anything from its native form generally creates artifacts and cross conversions only create cumulative errors. At this point, this debate has few players since HDMI 1.3 sources and receivers are rare at this time so I bring this up as a reference for my experience and a heads up to readers passionate about the new HD disc formats performing at their peak capability.</p>

<p><br />
<B>Problems</B><br />
None</p>

<p><br />
<B>Service</B><br />
This is one of those rare moments where I can report from direct experience. OPPO is great. I lost my OPDV-971HD during the warranty period. I called them up explaining I was a service center and they sent me a part! While it did not resolve the problem they deserve kudos for providing that potential convenience. I ended up having to ship it back but lo and behold they offer a prepaid service so I could simply order one and send the old one back for credit. If I was needy I could have also had them overnight one, naturally at my expense. Now that is service!</p>

<p><br />
<B>Putting It in Perspective</B><br />
I think the review says it all; you can't go wrong with this product at this price. To do significantly better will require far more money, well over $1000. What you would gain is better imaging for maybe 10-20% of the time and that kind of concern puts you in the videophile 5-6 figure home theater money league. For general everyday audio performance using an HDMI equipped receiver accepting linear PCM or analog multichannel inputs you have access to thousands of HD audio titles. If you are an audiophile though you can do far better and this is not the right product for such a demanding application.</p>

<p>With HD DVD and Blu-ray players hitting the market do you really need yet another box, remote and available connection to deal with? The first generation Toshiba HD DVD players did OK with upscaling but the OPPO is better. Blu-ray on the other hand has just hit the market from a variety of well known manufacturers who have their own history of decent performance as well as new HD DVD models from Toshiba supporting 1080p for both HD DVD and SD DVD and the difference in performance may not be enough to justify. For my system I have a first generation Toshiba HD DVD player, Xbox 360 and a Sony PS3. As pointed out, the Toshiba is not as good. The Xbox 360 is stuck at 480p and won't upconvert SD DVD for the standard analog component connection. Until recently it had an SD DVD black level error as well but that has been fixed. As this article was published the Sony PS3 was upgraded to support 1080p upconversion of SD DVD and based on a preliminary evaluation it directly competes with the OPPO's level of performance. For my application and passion for quality, the DV-981HD fits the requirement at the right price even though SD DVD is going by the wayside in my HD system.</p>

<p>What about the other models? It depends on the application. The DV-970HD is $149 and you can certainly save some dough if your display is not 1080p or does not support a 1080p input. That said, if 1080p is in your near future I would suggest the DV-981HD. If you are running an external scaler the DV-970HD does provide the raw 480i output from the disc for the best possible processing, costing far less than modified DVD players enabled for SDI. It is a natural for that application and also the intent of providing the feature! To be clear, this feature was not tested for this review. The OPDV-971HD is $199 and the main purpose to buy it is for a legacy display that uses DVI inputs. While DVI is compatible with HDMI there are various reasons as to why one might want DVI exclusively and such reasons will have been brought to your attention by your calibrator or HT installer due to your unique situation. An explanation here would be lengthy and apply to very few users.</p>

<p>Finally we come to the sad reality; your application and either one of the HD disc players may make the OPPO unnecessary. Starting at the bottom price range a Sony PS3 is $600 and a Toshiba HD-A20 about $450. Take the cost of an OPPO out of those prices and you can see the dilemma you have with price versus overall capability. In my opinion OPPO needs to get involved with the HD disc formats or they will be left with great SD DVD players that no longer fill a need if either or both HD disc formats blast off. As of today, due to the Sony firmware update for the PS3, I have no real need for the DV-981HD either for SD DVD.</p>

<p><br />
<B>Conclusion</B><br />
OPPO continues to give other far better known manufacturers a great deal of competition, not only at this price point but even higher ones hovering at $1k plus. While certainly not perfect nor to be expected at this price point it excels in what it does right and for what it does not do wrong providing very clean imaging for SD DVD videophile applications and decent sound for SACD and DVD Audio. Ultimately this product comes highly recommended for videophiles and most users!</p>

<p><br />
<b>Other Reviews</b><br />
<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.hometheaterhifi.com/cgi-bin/shootout.cgi?function=search&articles=124#OPPO%20DigitalOPDV971H%20(DVI)">OPDV-971HD Home Theater Secrets Review</a></p>

<p><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.hometheaterhifi.com/cgi-bin/shootout.cgi?function=search&articles=130#OPPO%20DigitalDV-970HD%20(HDMI)">DV-970HD Home Theater Secrets Review</a></p>

<p><br />
<b>References</b><br />
*<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.smr-home-theatre.org/surround2002/technology/page_07.shtml">Poking a round hole in a square wave</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>June 14, 2007 06:54 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 613
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
 				AND entry_id <> 613
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/06/oppo_dv-981hd_upconverting_sd_dvd_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
