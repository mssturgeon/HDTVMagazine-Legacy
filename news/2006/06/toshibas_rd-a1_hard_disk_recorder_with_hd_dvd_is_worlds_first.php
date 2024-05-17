<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 390";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Shane Sturgeon'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 390 AND placement_is_primary = 1";
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
	<meta name="keywords" content="hard disk, high definition, output line, audio output, video mode, DVD, dvd, video, high, mode, digital, output, hard, content, disk, recording, may, definition, line, discs, recorder, Toshiba, toshiba, Video, playback" />
	<meta name="description" content="Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &amp;quot;RD-A1&amp;quot; can record and store up to 130 hours of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc." />
	<title>HDTV Magazine Bulletins - Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Toshiba\'s RD-A1 Hard Disk Recorder with HD DVD is World\'s First'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2006/06/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Shane Sturgeon" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Toshiba's RD-A1 Hard Disk Recorder with HD DVD is World's First</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Shane Sturgeon</b><br />
				<?=$author_title?>
				Posted on <b>June 22, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Products & Equipment">Products & Equipment</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/06/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2006/06/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2006/06/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2006/06/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first.php&amp;phase=2&amp;title=Toshiba%27s%20RD-A1%20Hard%20Disk%20Recorder%20with%20HD%20DVD%20is%20World%27s%20First&amp;bodytext=Toshiba%20Corporation%20today%20unveiled%20the%20future%20of%20home%20video%20entertainment%20in%20an%20age%20of%20digital%2C%20high%20definition%20content%3A%20the%20world%27s%20first%20digital%20hard%20disk%20video%20recorder%20integrating%20a%20recordable%20HD%20DVD%20in%20combination%20with%20a%201-terabyte%20%28TB%29%20hard%20disk.%20The%20new%20%26quot%3BRD-A1%26quot%3B%20can%20record%20and%20store%20up%20to%20130%20hours%20of%20high-definition%20%28HD%29%20broadcasts%20on%20its%20high%20capacity%20hard%20disk%20and%20record%20up%20to%20230%20minutes%20of%20HD%20content%20to%20a%20single%20HD%20DVD%20disc.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<center><p align="center"><strong><font color="#996633" size="4">Integration of 1-Terabyte<sup>2</sup> Hard Disk with HD DVD Recordable Drive Opens way to Recording and Archiving of High-Definition Video</font></strong></p>
</center>

<p><img src="/images/articles/RD-A1.jpg" alt="RD-A1" align="left"><br />
<p>TOKYO-Toshiba Corporation today unveiled the future of home video entertainment in an age of digital, high definition content: the world's first digital hard disk video recorder integrating a recordable HD DVD in combination with a 1-terabyte (TB) hard disk. The new &quot;RD-A1&quot; can record and store up to 130 hours<sup>3</sup> of high-definition (HD) broadcasts on its high capacity hard disk and record up to 230 minutes of HD content to a single HD DVD disc. In addition to superb image and sound recording and playback, the new recorder also offers an extensive range of advanced functions made possible by the versatility of HD DVD, including optimized navigation and menu displays. The RD-A1 is scheduled for roll out in the Japanese market from July 14. </p>

<p>The RD-A1 is the first video recorder to support recording and playback of content in the HD DVD format<sup>4</sup>, the next generation of DVD format defined and approved by the DVD Forum. The recorder combines support for recording of full HD broadcasts with high capacity recording to HD DVD-R discs: up to 115 minutes<sup>3</sup> of HD content to a 15-gigabyte (GB) single-layer HD DVD-R disc, and up to 230 minutes<sup>3</sup> to a 30GB dual-layer HD DVD-R disc, allowing viewers to make HD DVD-R libraries of their favorite TV programs, whether dramas, movies or sport. Ease of use is also enhanced by the ability to record two TV programs, one digital HD and one analog, to the hard disk, simultaneously.</p>

<p>In addition to HD DVD, the RD-A1 also supports playback from and recording to conventional DVD-RAM/-RW/-R discs, giving users complete access to content recorded and saved in standard DVD. It also offers simplified transfer of DVD disc content to higher capacity HD DVD discs.</p>

<p>Another key feature among the many supported by the RD-A1 is support for 1080p output via HDMI, allowing viewing of &quot;full HD&quot; progressive scan video signals<sup>5</sup>. Up-conversion<sup>6</sup>of standard DVD to 1080p resolution output also enhances the enjoyment of current DVD software and recorded programs. Video and audio output is further enhanced by the design of the RD-A1's chassis, which isolates the player from vibration and optimizes the performance of its high-grade parts and components.</p>

<p>RD-A1 takes full advantage of the advanced functionality<sup>7</sup> offered by the versatility of the HD DVD format, which far surpasses standard DVD in its extensive support for &quot;pop-up menus&quot; and advanced features such as Picture in Picture (PIP) with moving picture functions.</p>

<p><strong><font color="#996600" size="+1">Background</font></strong><br />
<p>Toshiba launched &quot;RD-2000,&quot; the world's first digital video recorder integrating a hard disk and DVD recorder in the Japanese market in 2001. RD-2000 introduced the world to a new way of viewing TV programs, &quot;First record to hard disk, select and archive to DVD,&quot; and inspired a new market for &quot;Hard Disk &amp; DVD&quot; where Toshiba still provides leadership and drives growth. Now, as HDTV broadcasting expands its services and service area, in readiness for the 2011 phase out of analog broadcasting in Japan, demand is growing for an &quot;Hard Disk &amp; DVD&quot; solution that can handle high definition image quality and its larger data capacities. Toshiba delivers the clear answer with the RD-A1. The new recorder is the first HD DVD recorder, and combines it with a 1TB hard disk, and with it Toshiba leads the industry and supporting for HD DVD playbacks and recorders to be the first manufacture to bring the product in the market. Toshiba will enhance its line-up of HD DVD products by producing the products which integrates the next generation of DVD in the marketplace.</p></p>

<p><strong><font color="#996600" size="+1">Key Features of the New Recorder</font></strong><br />
<p><strong><font color="#996600">1. </font></strong><font color="#996600"><strong>Record digital broadcasts to hard disk and HD DVD-R</strong></font> Integrated digital tuners cover the full range of HD broadcasting sources &mdash;mdash; terrestrial, broadcast satellite (BS) and communications satellite 110&deg; (CS) broadcasts &mdash;mdash; while another dedicated tuner handles analog broadcasts. The RD-A1 can record two broadcasts at once, one digital broadcast, one analog. The hard disk drive's terabyte capacity allows it record and playback 130 hours<sup>3</sup> of HD broadcasts, and viewers are then free to select and archive their favorites to HD DVD discs. The RD-A1 supports two HD DVD-R capacities, a single-layer 15GB disc that can record up to 115 minutes<sup>3</sup> of HD broadcasts, and a dual-layer 30GB disc that doubles that performance to 230-minutes<sup>3</sup>, allowing viewers to build libraries of their favorite programs. The new recorder also supports recording of digital high-definition TV programs on conventional DVD-RAM/-RW/-R discs at standard definition image quality.<sup>8</sup></p></p>

<p><strong><font color="#996600">2. Playback of high definition content and support for advanced content features</font></strong><sup>7</sup> The RD-A1, like the HD DVD players Toshiba has already launched, can play back HD DVD content software, and also supports the enhanced functionality and diverse features that content providers can build into their package software &mdash;mdash; a major step forward from standard DVD players. While specifics depend on the title, typical features include the convenience of a &quot;pop-up menu&quot; that displays menu choices or movie chapters while a movie plays, allowing viewers to search for desired functions or use the chapter guide to jump to a particular scene. The new product also supports PIP with video, a feature that allows, for example, comments by the director or actors to be superimposed over a movie while it is playing. The commentators can literally point to the material they are discussing. &nbsp;Audio output is as rich as video playback, as RD-A1 supports next generation surround sound formats, such as Dolby Digital Plus, Dolby TrueHD and DTS-HD, L-PCM 5.1ch, the same formats as Toshiba's first HD DVD players. Analog 5.1ch output integrated into the RD-A1 allows consumers to enjoy surround sound simply by connecting the player to an AV amplifier with analog input.

<p><strong><font color="#996600">3. Support for RD engine for HD DVD</font></strong> Toshiba has upgraded its successful &quot;RD Engine HD&quot; to provide dedicated support for HD DVD format. Upgrades include a graphic user interface with letter-box display compatibility and Toshiba proprietary multi-function recording software. RD Engine HD allows viewers to edit recorded high-definition programs on a frame basis, and transfer the edited video to an HD DVD disc. A useful function is high-speed write of DVD video sources to hard disks and high speed dubbing<sup>9</sup> of that video to an HD DVD-R, allowing viewers to combine programs from multiple discs on a single disc. This compacting of video libraries is done without any loss of picture or sound quality. 

<p><strong><font color="#996600">4. Digital high definition picture of 1080p in HDMI output</font></strong> Support for the up-conversion to 1080p output is achieved<sup>10</sup> through implementation of the newest high performance scaler from Anchor Bay Technologies Inc. This converts and plays back 1080i HD content as 1080p full HD output<sup>11</sup>. Furthermore, besides the HD DVD software and DVD software, it is also possible to playback past recorded DVD by up-converting them to 1080p output<sup>12</sup>. 
<p><strong><font color="#996600">5. Body and parts designed for high definition picture quality and high quality sound </font></strong> The RD-A1's design is optimized for high quality video and audio output by a special dual-layer body featuring a 1-millimeter main case and a metal sub frame. The recorder stands on special aluminum pillars designed to damp vibration and enhance high sound quality. The same attention to detail carries through to chief components. The RD-A1 is the first recorder to adopt a high-speed, high-performance 297MHz/14bit video encoder, making it possible to deliver HD quality via analog output through the D terminal and component terminal. High grade parts typically found in high-end audio products are also used in the RD-A1. 

<p><strong><font color="#996600">6. Internet connectivity via &quot;Net de Navi<sup>&reg;</sup>&quot;software, recommendation service, and DLNA guideline</strong></font><sup>13</sup> The versatility of the RD-A1 is significantly enhanced by its Internet connectivity via &quot;Net de Navi<sup>&reg;</sup>&quot; software. Once in a network the recorder can be programmed remotely, via e-mail or the on-line iEPG, an electronic TV program timetable service. LAN connectivity allows configuration of a home network&nbsp; with Toshiba's series of digital high-definition LCD TVs in the &quot;REGZA Z1000&quot; series and with the &quot;Qosmio G30&quot; AV notebook PC, which supports DLNA guideline, and allows users to playback the recorded titles on different devices on the network<sup>14</sup>. 

<p>1 As of June 2006, as a digital video recorder with HD DVD.</p>

<p>2 1TB is 1,000GB (Gigabyte), calculated on the basis of 1GB=1 billion bytes.<br />
 <br />
3 Recording of digital terrestrial broadcasts at approx.17Mbps in TS mode.<br />
 <br />
4 Playback and recording in MPEG4 AVC and VC1 is not available for HD-DVD-R.<br />
 <br />
5 An HDTV or HD display equipped with D3/D4 input or HDCP capable HDMI input is required for high-definition viewing.</p>

<p>6 Since up-conversion is from standard definition video, image quality may not match that of an original high-definition source. <br />
 <br />
7 1) Some advanced functions may not be available, depending on HD DVD content specifications.<br />
 <br />
 2) HD DVD player support for versatile functions is based on software instructions integrated with the content. Such instruction may need to updated, via downloads from the Internet. Functions and operation, including the display, sound effect and icons, may differ with content. Consult the manufacturer's customer service or the user manual for more details.<br />
 <br />
 3) Please check content specifications of the content since some contents may not apply to the up-date information or may require a broadband Internet connection. </p>

<p>8 VR-mode recording is available in CPRM discs. Support for cartridge in DVD-RAM. Also, support for VR-mode recording in DVD-R DL.<br />
 <br />
9 No support for copy-once recording. Limited to titles recorded in VR mode, or titles recorded in the Toshiba RD series products supporting re-writing.<br />
 <br />
10 In order to enjoy viewing 1080p output signals, a cable and a TV or display that support the 1080p signal format is required. </p>

<p>11 Some content may not up-convert. </p>

<p>12 Since up-conversion is from standard definition video, image quality may not match that of an original high-definition source.</p>

<p>13 DLNA (Digital Living Network Alliance) is a organization that supports standardization of home LAN.&nbsp; Supports only the digital media server.</p>

<p>14 Copy free, limited to titles in VR mode. </p>

<p><font color="#996600"><strong><font size="+1">Key Specifications</font></strong></font> 
<table class="type1b" cellpadding="0" cellspacing="0"><tr><td class="type1b_header" nowrap >Model name</td><td class="grid">RD-A1</td></tr><tr><td class="type1b_header" nowrap >Hard Disk&#12288;</td><td class="grid">Built in Hard DiskHD DVD-Video
Twin Format Discs (Dual-layer, single-sided, HD DVD-Video + DVD-Video), HD DVD-R (HDVR mode), HD DVD-R DL (HDVR mode), DVD-R (Video mode; VR mode), DVD-R DL (Video mode; VR mode), DVD-RAM (VR mode), DVD-RW (Video mode; VR mode), DVD-Video Music CD CD-R, CD-RW (CD-DA), <table> <tr> <td nowrap valign="top">*</td> <td>Some content and discs may not be compatible. Depending on recording mode and condition, some discs may not playback. DVD-RW Ver.1.0 is not supported.</td> </tr> </table></td></tr><tr><td class="type1b_header" nowrap >Recordable media <p></p>
</td><td class="grid" class="grid"> <p>Built in Hard Disk (TS mode; VR mode) HD DVD-R (HDVR mode), HD DVD-R DL (HDVR mode), DVD-R (Video mode; VR mode), for General/1X-16X SPEED (recording speed 8X), DVD-R DL (Video mode; VR mode), for General/4X SPEED (recording speed 4X), DVD-RAM (VR mode), 2X-5X SPEED (Applied cartridges), (recording speed 2X), DVD-RW (Video mode; VR mode), 1X-6X SPEED (recording speed 4X), *Some discs may not be compatible, or may not record.</td></tr><tr><td class="type1b_header" nowrap >Video recording format&#12288;</td><td class="grid">MPEG 2&#12288;</td></tr><tr><td class="type1b_header" nowrap >Audio recording format</td><td class="grid"><p>Dolby Digital (2ch), L-PCM (2ch), AAC (5.1ch),</p></td></tr><tr><td class="type1b_header" nowrap >Audio output</td><td class="grid">Dolby Digital (5.1ch)
Dolby Digital Plus (5.1ch), Dolby TrueHD (2ch), DTS-HD (5.1ch) L-PCM (5.1ch) *Output format depends on content.</td></tr><tr><td class="type1b_header" nowrap >Video DAC</td> <td class="grid">14bit, 297MHz</td></tr><tr><td class="type1b_header" nowrap >Audio DAC&#12288;</td> <td class="grid">192kHz, 24bit</td></tr><tr><td class="type1b_header" nowrap >Channel&#12288;</td><td class="grid">Digital terrestrial broadcast (000 - 999ch), CATV path through,BS digital (000 - 999ch), 100 degree CS digital broadcast (000 - 999ch), Analog terrestrial broadcast VHF (1 - 12ch), UHF (13 - 62ch), CATV (C13 - C63ch),
</td></tr><tr><td class="type1b_header" nowrap >Input</td><td class="grid">S1 video input line 3 (rear 2, front 1), video input line 3 (rear 2, front 1), 2ch analog audio input line 3 (rear 2, front 1), D1 video input line 1, DV input line 1 (front)</td></tr><tr><td class="type1b_header" nowrap >Output&#12288;</td><td class="grid">D1/D2/D3/D4 video output line 1, component video output line 1 (Y, CB, CR), S1 video output line 3, video output line 3, 5.1ch surround-sound analog audio output line 1, 2ch analog audio output line 3, Coaxial digital audio output line 1, Optical digital audio output line 1, HDMI output line 1, i.LINK line 2 (D-VHS dubbing)</td></tr><tr><td class="type1b_header" nowrap >Antenna terminal</td> <td class="grid">Digital terrestrial in-output, BS/100degree CS digital in-output, VHF/UHF in-output</td></tr><tr><td class="type1b_header" nowrap >Other terminals&#12288;</td> <td class="grid">LAN terminal, Sky Perfect continuous terminal, phone circuit terminal, Extension terminal line 3 (front, 5V 500mA)</td></tr><tr><td class="type1b_header" nowrap >Power consumption&#12288;</td><td class="grid">133W (BS antenna supply:144W), Stand by mode 6.5W (power-save:4.0W),</td></tr><tr><td class="type1b_header" nowrap ><p>Dimensions</p></td><td class="grid">Width 457 mm &times; Height 159 mm &times; Depth 408 mm</td></tr><tr><td class="type1b_header" nowrap >Weight&#12288;</td><td class="grid">15.2 kg&#12288;</td></tr><tr><td class="type1b_header" nowrap >Accessories&#12288;</td><td class="grid">Remote control, battery for remote control (AAA cell battery x 2),power cable, coaxial cable, video/audio connecting cord, user manual, B-CAS card, modular splitter, phone cable</td></tr></table>

<p><br />
<table><tr><td valign="top" nowrap>&#12539;</td><td>RD-A1 supports AACS (Advanced Access Content System), the next generation content protection &nbsp;system.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>An HDTV or HD display equipped with D3/D4 input, HDCP capable HDMI input, or component video input is required for high-definition viewing. Other TVs or displays can display content, but not in high definition. Also, some content may not playback or playback in lower resolution on equipment with D3/D4 and component video output.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>HDMI and High-Definition Multimedia Interface are trademarks of HDMI Licensing, L.L.C.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>HD DVD and DVD are trademarks of the DVD Format/Logo Licensing Corporation.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Dolby and Dolby Digital are registered trademarks of Dolby Laboratories.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>DTS is a registered trademark of DTS, Inc.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>i.LINK is a registered trademark of Sony Corporation.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Other company names and products names are the registered trademarks of each company.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Service via the Internet may subject to temporary cessation or termination without notice.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Some discs may not playback or record.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Some discs may be incompatible with playback and/or record.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Recording of audio and video is for personal use and unauthorized usage is prohibited under the copyright laws.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>While Toshiba has made every effort at the time of publication to ensure the accuracy of the information provided herein, product specifications are subject to change without notice.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Not all discs and output connectors are compatible and no guarantee of performance is made hereby.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Actual writing speed may decrease from the actual speed.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>This digital AV product integrates diverse software. The hard disk and HD DVD drive are connected via an ATAPI interface, a PC-based connection standard, and both the hardware and software are managed via operating system software, like a PC.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>While designed to be robust, the hard disk contains moving parts, and may be damaged if proper conditions of use are not observed. If a part of the hard disk platter becomes damaged, programs recorded on that part may exhibit pixelation or black noise when played back. If you notice such noise or problem, you will have to replace the hard disk, at cost.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>We do not recommend using the hard disk for long term storage of programs. Transfer important programs that you want to save to a recordable DVD disc. Recordable DVD discs are also susceptible to damage if not handled and stored carefully, and as a result some of all of the programs stored on them may become unplayable. Reduce these risks by using high quality DVD recordable discs and checking their playability from time to time. Recovery of programs deleted from the hard disk is not guaranteed.</td></tr><tr><td valign="top" nowrap>&#12539;</td><td>Hard disk capacity is calculated on the basis of 1TB=1,000GB, and 1GB =1-billion bytes.</td></tr></table></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Shane Sturgeon</b>, <b>June 22, 2006 05:30 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 390
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
 			<h2>More on Products & Equipment</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Products & Equipment'
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
 				AND entry_id <> 390
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2006/06/toshibas_rd-a1_hard_disk_recorder_with_hd_dvd_is_worlds_first.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
