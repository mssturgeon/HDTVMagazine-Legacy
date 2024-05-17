<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 382";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 382 AND placement_is_primary = 1";
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
	<meta name="keywords" content="video processing, front projectors, equipment chain, dvd players, frame rate, projector, fps, video, Fps, player, processing, film, resolution, chip, DVD, equipment, might, dvd, content, progressive, frame, could, dlp, DLP, output" />
	<meta name="description" content="In this article, I will introduce some of new 1080p front projectors with full 1920x1080p resolution, one from JVC D-ILA, another from Sony using similar LCoS technology (named SXRD) breaking the ground at $10,000 with high reviews (Ruby), and to wrap up, I will introduce four DLP front projectors implementing Texas Instruments' new true 1080p DLP DMD with 2 million plus mirrors (not &quot;wobulated&quot; as the earlier 1080p solutions). Some of these products are not yet available.

Affordable 1080p front projection is finally here, the time might be right to start that Home Theater HD project of your dreams. Projectors with 1080p inputs can now be paired with Blu-ray players with 1080p outputs, which transport the 1080p content of Blu-ray discs. Hopefully, that feature would also be implemented in 2nd generation HD DVD players, the other format, in the near future, according with Toshiba." />
	<title>HDTV Magazine Articles - Why 1080p? - Part 3 - Front Projectors</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/why_1080p_-_part_3_-_front_projectors';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Why 1080p? - Part 3 - Front Projectors'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Why 1080p? - Part 3 - Front Projectors</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>May 25, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php&amp;phase=2&amp;title=Why%201080p%3F%20-%20Part%203%20-%20Front%20Projectors&amp;bodytext=In%20this%20article%2C%20I%20will%20introduce%20some%20of%20new%201080p%20front%20projectors%20with%20full%201920x1080p%20resolution%2C%20one%20from%20JVC%20D-ILA%2C%20another%20from%20Sony%20using%20similar%20LCoS%20technology%20%28named%20SXRD%29%20breaking%20the%20ground%20at%20%2410%2C000%20with%20high%20reviews%20%28Ruby%29%2C%20and%20to%20wrap%20up%2C%20I%20will%20introduce%20four%20DLP%20front%20projectors%20implementing%20Texas%20Instruments%27%20new%20true%201080p%20DLP%20DMD%20with%202%20million%20plus%20mirrors%20%28not%20%22wobulated%22%20as%20the%20earlier%201080p%20solutions%29.%20Some%20of%20these%20products%20are%20not%20yet%20available.%0A%0AAffordable%201080p%20front%20projection%20is%20finally%20here%2C%20the%20time%20might%20be%20right%20to%20start%20that%20Home%20Theater%20HD%20project%20of%20your%20dreams.%20Projectors%20with%201080p%20inputs%20can%20now%20be%20paired%20with%20Blu-ray%20players%20with%201080p%20outputs%2C%20which%20transport%20the%201080p%20content%20of%20Blu-ray%20discs.%20Hopefully%2C%20that%20feature%20would%20also%20be%20implemented%20in%202nd%20generation%20HD%20DVD%20players%2C%20the%20other%20format%2C%20in%20the%20near%20future%2C%20according%20with%20Toshiba.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<blockquote>This article is the third in a series.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/01/why_1080p.php">Why 1080p?</a><br>
Part 2: <a href="/articles/2006/01/why_1080p_-_part_2_-_a_brilliant_case.php">Why 1080p? - Part 2 - A Brillian(t) Case</a></blockquote>
<br>
<br>
In this article, I will introduce some of new 1080p front projectors with full 1920x1080p resolution, one from JVC D-ILA, another from Sony using similar LCoS technology (named SXRD) breaking the ground at $10,000 with high reviews (Ruby), and to wrap up, I will introduce four DLP front projectors implementing Texas Instruments' new true 1080p DLP DMD with 2 million plus mirrors (not "wobulated" as the earlier 1080p solutions). Some of these products are not yet available.

<p>Affordable 1080p front projection is finally here, the time might be right to start that Home Theater HD project of your dreams. Projectors with 1080p inputs can now be paired with Blu-ray players with 1080p outputs, which transport the 1080p content of Blu-ray discs. Hopefully, that feature would also be implemented in 2nd generation HD DVD players, the other format, in the near future, according with Toshiba.</p>

<p>Because Hi-def players have the potential to supply a hi-end 1080p signal to a 1080p projector I will cover a bit of the Hi-def DVD subject; although this article is dedicated to 1080p front projectors, most of the discussion of 1080p connectivity and video processing would apply to other 1080p displays as well.</p>

<p>For those readers that do not know, there are two competing formats in the Hi-def DVD market, one is Blu-ray, the other is HD DVD. Both formats use blue laser, discs with film based content of both formats have progressive 1080p/24Fps capabilities, and both types of players are backward compatible to play regular DVDs upconverted to 1080i/p resolution over permitted connections, such as HDMI (although HD DVD players are upconverting DVD to just 1080i for the moment).</p>

<p>Toshiba just released two HD DVD players that are capable to read the 1080p resolution of the HD-DVD disc but output the HD signal as interlaced 1080i60 fields x second, which means the projector can not receive the 1080p content from a HD DVD disc due to a limitation of the player.</p>

<p>Since the primary objective of most Home Theaters is to watch movies, and most movies are sourced from 24fps film and telecined into 1080p video, it is preferable that the equipment that plays the video is able to communicate with the projector in 1080p with minimum conversions.</p>

<p>A 1080p24Fps movie displayed at that speed would show objectionable flicker. To avoid that flicker, your local Movie Theatre displays the 24 celluloid frames at double the speed by opening the film-projector's shooter twice for each frame, to total 48 frames per second. In progressive video, a projector would have to do something similar although not necessarily at that speed, usually at 60.</p>

<p>Ideally, between the disc and the image displayed by the projector the equipment chain should avoid unnecessary interlacing conversions and video processing when processing 1080p film-based content. This requires that all connections of the equipment chain maintain the 1080p progressive format and resolution, within the player, at its output, on the cables, on the input stage of the projector, etc.</p>

<p>In other words, not having 1080p outputs on the player would mean that film originated content is degraded from its original progressive form to an interlaced 1080i version using 2:3 pulldown (more below).</p>

<p>However, having 1080p outputs on a player not necessarily means problem solved. The frame rate output of the player might not be compatible with the frame rate the projector accepts; the player might output a 60Fps up-framed version processed internally while the projector expects the original 24Fps; the reverse could be true.</p>

<p>However, if both pieces offer multi frame rate capability it allows for testing to determine which one does what best, but it could also mean an impact of the expected overall 1080p performance if a handshake is reached by altering settings and not necessarily obtaining the best of their capabilities, individually and/or as a system.</p>

<p>When selecting players and projectors (and scalers) check the features, frame rates, resolutions, input/outputs, etc. before assuming the 1080p player would provide the perfect conditions you expect for your 1080p projector. For example, Sony announced their player to output everything as 1080p 60Fps, but Pioneer indicated 1080p 24Fps (which matches well with their Elite plasmas), while HD DVD players do not output 1080p at all.</p>

<p>If using scalers in between player and projector it would be ideal for the 1080p signal to pass-thru the scaler to avoid interlace processing, some scalers have pass-thru, some do not, in such case a direct connection to the projector bypassing the scaler could be the best choice for film content. However, the 1080p progressive signal could end up handled by an undefeatable interlace conversion before the projector displays it as 60p. Additionally, a scaler might help resolve a frame matching problem better than the other pieces if they multi-frame capable.</p>

<p>In other words, the conversion performs 2:3 pulldown processing to convert the 24Fps progressive source into 30 frames of 60i interlaced fields (adding the 6 missing frames as 12 fields), and then converts those 60 interlaced fields to 60p full frames, a displaying speed typically used for 1080p front and rear projection (and 1080p panels). Unfortunately, this might be not the best processing choice available in the equipment chain in order to preserve the quality of 24Fps film progressive content.</p>

<p>It could be better to maintain the progressive cadence of the film from the disc to the projector, and let the projector multiply the frame rate in the progressive domain. As mentioned above, some Pioneer Elite plasmas are known to be able to perform such functionality, the plasma panel accepts 24Fps but displays it as 72Fps, doing what they call 3:3 pull-down or Pure Cinema. The Brillian LCoS RPTV recently reviewed on the part II of this series displays at 120Fps, 5 times the 24Fps speed of the original film content.</p>

<p>"Best and ideal" equipment is not easy to find and to match because although the selection of equipment with 1080p capabilities is growing, it is still limited. There might be no other choice than to accept some connectivity limitations on the player, the projector, and/or the scaler in between, as well as accepting undefeatable interlaced conversions when handling 24fps film based material.</p>

<p>Regarding content originated as 1080i interlaced video, it would not need to go thru such 2:3 pull-down processing because it already has the 60i fields. However, to display 60i on 1080p projectors the 60i fields need to be deinterlaced and doubled as 60p frames. For performing that task, the assigned piece of equipment might/not be suited with certain functionality, such as motion-adaptive deinterlacing, pixel by pixel, motion calculation with less or more fields in advance, video processing chip used, etc.</p>

<p>The overall result of the system could be impacted if the job is assigned to a player that lacks the features, and the scaler/projector has them but they are bypassed. In other words, one piece of the equipment chain might perform that function better than the rest; take your time in choosing it correctly.</p>

<p>If the projector is assigned to perform the deinterlacing, the connection of player/projector would then be as 1080i, therefore, there is no gain in looking for absolute perfect 1080p connectivity for that particular application.</p>

<p>If there is a scaler in between player and projector and is assigned to perform the deinterlacing job, the connection between player and scaler would be 1080i, the scaler deinterlaces and doubles to obtain 1080p/60Fps, outputs it that way, and the projector accepts it as 1080p/60Fps and maps the image to its chip to display it usually at the same speed of 60; those two pieces should connect as 1080p (and assumes the projector accepts 1080p/60Fps).</p>

<p>As you see there might be several possibilities in the task of improving the overall 1080p picture, you might want to test each piece of the equipment chain to find the best combination of video processing, and that would only be possible if those pieces offer a variety of video processing capabilities and connectivity (1080i/p, various frame rates, etc).</p>

<p>Although I did not mention 720p as alternative to the lack of 1080p inputs/outputs it should be noted that some equipment outputs and inputs offer 720p capability.</p>

<p>Upscaling 720p to 1080p is viewed by some as a better choice than deinterlacing 1080i to 1080p. I particularly feel that reducing the spatial original resolution from 1920 of the disc to 1280 of the 720p transport format, to later upconvert it back to 1920 with interpolated pixels (the original pixels were already lost) for the final 1080p display, is a higher price to pay than the benefit of maintaining the 60fps temporal resolution of the 1080p/720p/1080p vertical resolution conversions of the p formats, specially considering that film content was the primary purpose for 1080p HT viewing, in other words: movies, no rapid sport videos.</p>

<p>Let us make the introductions:</p>

<p><img src="/images/articles/dla-hd10k.jpg" alt="DLA-HD10K" align="right"><b>JVC</b><br />
Introduced Feb 06<br />
<u>New Flagship</u><br />
DLA-HD10K $25,000, TTM now, 3-chip D-ILA 'non-moving' mirror reflective technology, 1920x1080p, <u>accepts 1080p 48/50/60 fps over DVI-D,</u> high-resolution lenses with motorized zoom and focus with a 0-60% vertical offset, two models: a long throw with a lens throw distance of 2-3.8:1 (placement of the projector at the back of the theater), for a 10-foot screen, the projector could be placed anywhere from 20 to 38 feet from the screen, and a short throw model with lens throw distance of 1.5 - 2.0:1 to facilitate projector to be used for CRT replacement or rear screen applications, 2500:1 CR, 27db quiet fan noise, user-replaceable lamp with 2000 hrs life at $500. Faroudja, Silicon Optix, and Anchor Bay Technologies are planned to supply three different external digital signal processor packages with this model.<br clear=all><a href="http://pro.jvc.com/prof/Attributes/features.jsp?tree=&amp;model_id=MDL101568&amp;itempath=&amp;feature_id=01">http://pro.jvc.com/prof/Attributes/features.jsp?tree=&amp;model_id=MDL101568&amp;itempath=&amp;feature_id=01</a></p>

<p><img src="/images/articles/vpl-vw100.jpg" alt="VPL-VW100" align="right"><b>Sony</b><br />
VPL-VW100 (Ruby)<br />
Shown again but not demo at CES was this Sony's projector using SXRD technology, TTM Nov 05, $10,000, 1080p 3x0.61" panels, little brother of Qualia 004, 15,000:1 CR with Advanced Iris Function on, 400-watt Pure Xenon Lamp ($1,000), <u>accepts 1080p/60fps over DVI and HDMI</u>, low fan noise 22dB, 1.8X Zoom, Lens Shift, DRC-MFv2, vertical keystone, auto input search, projection picture size 40 to 300 inches diagonally, although Sony recommended not larger than 120" (as below), projector shown at right.</p>

<p>At the SONY booth at CES 2006 the rep seemed to know this projector very well, he indicated that, in his view, the projector performs better scaling and video processing than most external scalers, he was not sure if the projector would disable the internal down conversion to 1080i when feeding 1080p to its input. It uses pixel-by-pixel motion adaptation deinterlacing. He recommended a Stewart white/gray screen not larger than 120" and 1.3 of gain; the Firehawk and DaLite screens were said to work well, with a minimum distance of 9" for maximum brightness. I viewed this projector several times in various environments and 90-110" screens, I consistently noticed the great resolution, but accompanied of a deficient light output for my taste. I suppose that the low light output could certainly please HT fans that love film Movie Theater environments, but I particularly prefer more lumens, and would rather choose a brighter projector such as the Optoma HD81, as long as the resolution and video quality could be equal or higher than the Sony, which looked that way at CES.</p>

<p><b>Texas Instruments</b> has recently released a consumer DMD DLP chip with 2+ million mirrors, one per pixel for the full 1920x1080p HDTV resolution. The chip is targeted initially to the front projector market. Check all the new products in the DLP section. Most 1080p DLP implementations use a 960x1080 chip to produce a 1920x1080 image, the chip that has half the mirrors of the image pixel count. The DLP engine uses a mirror tilting technique at double the speed to complete the full 2 million-image pixels in two horizontal image shifts of 1 million mirror reflections each ("wobulation").</p>

<p><img src="/images/articles/dlp-booth.jpg" alt="DLP Booth" align="left">According to TI, the human eye would see the two images as one at that speed. The technique was criticized by the competition because it did not use a chip with the two million-pixel mirrors, as the other technologies do, such as LCoS (Sony's SXRD, JVC's D-ILA, eLCOS, etc). TI did not disclose any plans to supply a similar chip for RPTVs, and commented that it was a market/manufacturer decision to request to TI 1080p chips if they are demanded for RPTVs, likewise, no announcements were made by any DLP set manufacturer of RPTVs regarding new lines using this new chip.<br />
<br clear=all><br />
Some CES demos of front projectors using the new two million-mirrors-1080p-chip were stunningly good, like the <b>Optoma HD81 1080p</b> ($10,000, TTM 3Q06, below) on a 135" screen, probably the <u>best 1080p FP in the price range</u>.<br />
<br clear=all><br />
<img src="/images/articles/hd81.jpg" alt="Optoma HD81 1080p" align="left"><br />
<br clear=all><br />
<img src="/images/articles/xv-z20000.jpg" alt="Sharp's XV-Z20000 DLP 1080p" align="left"><b>Sharp's XV-Z20000 DLP 1080p</b>, near future flagship model.<br />
$TBA (rumored at $12,000), TTM 3Q06, 1920x1080p resolution, Sharp's CV-IC III Video Scaling Circuitry, DVI/HDCP and HDMI inputs, 1000 ANSI, 10000:1 CR. Excellent demo with Blu-ray at CES (below), will <u>accept 1080p</u> when released, (left).<br />
<br clear=all><br />
<img src="/images/articles/vp-11s1.jpg" alt="VP-11S1" align="right"><b>Marantz DLP 1080p</b> new projector VP-11S1, TTM TBA, $ TBA, shown as prototype, 700 ANSI, 5000:1 CR, 2 HDMI, 2 component, Gennum video processing (right).<br />
<br clear=all></p>

<p><b>Projection Design</b><br />
<img width=312 height=173 src="/images/articles/action-model-3.jpg" alt="Action model 3 1080" align="left"><br />
Action model 3 1080<br />
True 1080p single DC3 DMD 0.95", Crystalio II (according to them the world's most technologically advanced video processor) with 4th generation broadcast quality algorithms for superior SD and HD video image quality, dual 7 segment color wheels and light formatters, DuArch illumination architecture featuring dual lamps, TI's BrilliantColor SLR technology, 24/7 operation warranty, Gennum's VXP Visual Excellence Processing, adjustable output brightness from 550 to 2500 ANSI lumens.</p>

<p>Stay tuned to the part IV of this "Why 1080p?" series, we will go deeper into the soon to be available Optoma HD81, a star in the CES 2006 show.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>May 25, 2006 09:01 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 382
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
 				AND entry_id <> 382
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/why_1080p_-_part_3_-_front_projectors.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
