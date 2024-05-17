<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 404";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 404 AND placement_is_primary = 1";
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
	<meta name="keywords" content="silicon image, hdmi hdmi, component analog, content protection, digital connectivity, hdmi, HDMI, digital, could, dvi, version, audio, DVI, video, HDCP, color, bandwidth, content, hdcp, image, support, standard, used, uncompressed, gbps" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/images/hdmi_200.gif&quot; alt=&quot;HDMI&quot; align=&quot;right&quot;&gt;Ever wonder what HDMI specs came along with which versions? Or why HDMI came along at all when there are so many connection types already from which to choose? Get all the details in this article, the first of a 10-part series on HDMI ... and which standards &amp; devices you should have in your home theater." />
	<title>HDTV Magazine Articles - HDMI - A Digital Interface Solution</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdmi_-_a_digital_interface_solution';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDMI - A Digital Interface Solution'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDMI - A Digital Interface Solution</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>July 25, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php&amp;phase=2&amp;title=HDMI%20-%20A%20Digital%20Interface%20Solution&amp;bodytext=%3Cimg%20src%3D%22http%3A%2F%2Fwww.hdtvmagazine.com%2Fimages%2Fhdmi_200.gif%22%20alt%3D%22HDMI%22%20align%3D%22right%22%3EEver%20wonder%20what%20HDMI%20specs%20came%20along%20with%20which%20versions%3F%20Or%20why%20HDMI%20came%20along%20at%20all%20when%20there%20are%20so%20many%20connection%20types%20already%20from%20which%20to%20choose%3F%20Get%20all%20the%20details%20in%20this%20article%2C%20the%20first%20of%20a%2010-part%20series%20on%20HDMI%20...%20and%20which%20standards%20%26%20devices%20you%20should%20have%20in%20your%20home%20theater.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><b>HDMI Part 1 - A Digital Interface Solution</b></p>

<p><img src="/images/hdmi_200.gif" alt="HDMI" align="left">There is a lot to talk about HDMI for one article, so I will cover the subject in 10 articles each addressing a different area of HDMI. This first article is about specs and versions. A special request made by Shane Sturgeon, our Magazine Chief Technologist & Co-Publisher, gave me the idea of covering other areas of HDMI not well covered by the press that often prompt Magazine readers to ask for help on the Tips list and the Forum.</p>

<p><br />
<h2>HDMI, What is it for you?</h2><br />
Many people talk about HDMI as a cable, or as a spec, or as a chip, as a simplification of digital connectivity, as the end of HD content protected viewing for 10 million early adopters of HDTVs with only component analog connections, as a de-facto standard adopted by hundreds of manufacturers, as a connection that sometimes causes more problems than it resolves, etc. Well, it is all of the above; and depending on a person's agenda, he/she might be very unyielding about some of that list and entirely ignoring the others.</p>

<p><br />
<h2>HDMI, How was it created?</h2><br />
<img src="/images/silicon-image.gif" alt="Silicon Image" align="right">On December 9, 2002, the seven founders of HDMI (High-Definition Multimedia Interface) announced the 1.0 specification of this connectivity standard, the enhanced, more robust form of DVI (see below). The seven founders are Hitachi, Matsushita, Philips, Silicon Image, Sony, Thomson, and Toshiba.</p>

<p>The standard supports HD uncompressed video, 8-channel digital audio (reportedly up to 192 KHz), and some control signals, all on a single cable (15 mm, 19 pin), while using less than half the available bandwidth of HDMI. HDMI was created with the same video capacity as DVI, or up to five Gbps of bandwidth, double what a HD signal would require, and is backward compatible with DVI by using an adapter, although that is for the video part only since DVI does not handle audio.  With the newest version of HDMI announced in June of 2006, HDMI has doubled its bandwidth capacity to 10.2 Gbps.</p>

<p>HDMI is quickly replacing DVI and is being implemented already on many products, and is becoming the de-facto standard for transporting uncompressed signals over a cable.</p>

<p><br />
<h2>HDCP (High-bandwidth Digital Content Protection)</h2><br />
This article is not intended to cover the details of HDCP.</p>

<p>The HDCP 1.0 specification was developed by Intel with contributions from Silicon Image in February 2000 to protect DVI outputs from being copied by providing a secure link between a video source and a display device.</p>

<p>HDCP offers authentication, encryption, and renewability. The Motion Picture Association of America (MPAA) endorsed HDCP as the standard for the secure transmission of HD signals over DVI, and is used on HDMI as well.</p>

<p><br />
<h2>A Quick Summary of the Ancestor (DVI)</h2><br />
The DVI (Digital Visual Interface) 1.0 specification was introduced in April 1999 by the Digital Display Working Group integrated by Silicon Image, Intel, Compaq, Fujitsu, Hewlett-Packard, IBM and NEC for the purpose of creating an digital connection interface between a PC and a display device. It is a connection with enough bandwidth for <u>uncompressed</u> HD video signals.</p>

<p>The 1.0 DVI specification is a point-to-point solution that supports video content but not audio, HDMI does. DVI uses the Transition-Minimized Differential Signaling (TMDS) protocol developed by Silicon Image. PanelLink is the Silicon Image's proprietary implementation of TMDS.</p>

<p>More background and specifications can be found on a Digital Connectivity Tutorial I wrote for this Magazine at:</p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/04/digital_connect.php">http://www.hdtvmagazine.com/articles/2006/04/digital_connect.php</a></p>

<p>This tutorial is also included as a separate section on every Yearly HDTV Technology Report, also found on this Magazine at:</p>

<p><a href="http://www.hdtvmagazine.com/reports/hdtv-technology-review.php">http://www.hdtvmagazine.com/reports/hdtv-technology-review.php</a></p>

<p><br />
<h2>Do we need HDMI?</h2><br />
Maybe our need for simplified cabling is not as much as the motivation of the Motion Pictures Association of America (MPAA) to protect their content, but today's digital world is increasingly in demand for more efficient and secured ways of accessing, distributing, and managing digital content any time any where; and HDMI has become the connection of choice by the HDTV industry for our living rooms.</p>

<p>Component analog and Firewire connections have their place as well.</p>

<p>Component analog was used as the only HD video connection on the first 5 years of HDTV adoption (1998-2003), the problem is that it is unprotected, and for that reason content providers preferred DVI or HDMI as a more secured method to transport an HD signal from a source equipment to a display device (TV, projector, panel).</p>

<p>IEEE1394 Firewire, also covered in the Digital Connectivity Tutorial, is being used for transporting HD <u>compressed</u> signals in digital form for a network or for recording devices, such as D-VHS and external DVRs. Firewire was implemented using a form of content protection called DTCP (also called 5c, for the five companies that found the standard).</p>

<p>One could efficiently record a compressed HD video signal with a bandwidth of 19.4 Mbps transported over Firewire, but it would not be practical to try to record its uncompressed form with approximately 2.2 Gbps of bandwidth, and even if someone wants to try that, DVI or HDMI with HDCP content protection would not allow it.</p>

<p>How could it take 2.2Gbps? The 1080i HD format has 1125 total lines of 2200 pixels x frame (active image 1080x1920), there are 30 frames per second on 1080i, requiring 74.25 MHz/pixels (1125 x 2200 x 30fps). Each pixel contains data for RGB and is implemented by DVI/HDMI with 30 bits (8 per each color plus another 6 for encoding). A 1080i HD 74.25 MHz/pixel signal would require 2.2 Gbps speed rate. Try to transport a 1080p/60fps signal and it could double up that bandwidth requirement.</p>

<p>Many modern HDTVs have the 3 types of connections for backward compatibility and for the different purposes they support, but there are still many earlier generation HDTV sets on the consumer hands (about 10 million) that could be resold as used equipment that only has component analog connections, or could still be used on other rooms of the house.</p>

<p>Beware; it could happen that a protected program running over such unprotected analog connection would not be able to be viewed as HD, even when you pay for the rightful viewing of the content (as PPV, VOD, or premium channel). I cover this subject in depth on this article:</p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv.php">http://www.hdtvmagazine.com/articles/2006/02/analysis_of_dtv.php</a></p>

<p>So hello HDMI, the path seems inevitable, for now.</p>

<p>HDMI has been able to transport the maximum HD quality of 1080p since day one in an uncompressed manner, the digital storage capacity required by a 2hr movie at 1080p quality is by itself overwhelming even to today's storage dreamers, and with the added HDCP content protection protocol (courtesy of Intel) HDMI/HDCP is considered very secured.</p>

<p>However, nothing could be absolutely secured anymore in the world, it is a matter of time and human will, many protection methods might be broken eventually. With the rapid advances of high capacity storage facilities, storing uncompressed HD video could one day be as cost effective as storing a CD is today, and some day a teenager in a corner of the world might claim that even HDCP could be circumvented.</p>

<p>He would then write the instructions of how to do it on the bottom of his skateboard, or publish it on the web as it happened already with DVD and other cases. Morals and human intelligence when used for a negative purpose are always in a constant clash to live in harmony, and HDMI/HDCP could be challenged as well.</p>

<p><br />
<h2>HDMI version 1.3, the Excuses</h2><br />
People are talking about version 1.2 and version 1.3 and why they prefer to wait to get blah-blah features. Version 1.3 has been officially out for a few weeks already, the availability of chips and audio/video equipment using those chips is another story, it could take months, some say equipment could be using 1.3 chips by Christmas, Sony's PS3 expected by November claims it would have it.</p>

<p>I recently received an invitation from Leslie Chard, President of HDMI Licensing LLC, upon their introduction of version 1.3 in June of this year. Leslie had the courtesy to give me a private presentation before the official release to the press; the information below was taken from the presentation.</p>

<p>I also used the opportunity to exchange ideas about several subjects regarding HDMI, including some issues people are having when using HDMI, which took away some of the glowing image of HDMI, some with merit some without.</p>

<p>There are some rumors running about version 1.3 as the only one linked to 1080p, and that earlier versions would not be 1080p capable. One should not condone a manufacturer that installed a non-1080p HDMI chip regardless of the version into a $39 DVD player that just needs to output 480i/p, why? It does not have any use for the 1080p capability.</p>

<p>However, what is not right to some 1080p interested consumers is that equipment that claims 1080p handling capability be suited with a non-1080p HDMI chip that bottlenecks such ability.</p>

<p>In some cases, the TV might actually have the 1080p capable HDMI chip but might not have the proper TV design to internally handle 1080p between the chip and the final display of the image. Many first generation 1080p HDTVs recently introduced do not accept 1080p due to these reasons; cost decisions, market choices, etc. not the HDMI spec, any version.</p>

<p>It is unfair to HDMI that many manufacturers were blaming the unavailability of the HDMI 1.3 spec for their inability to accept 1080p on their 1080p TV sets, when actually even the version 1.0 spec was capable to handle such resolution, obviously the proper chip was not installed.</p>

<p>A similar situation could be mentioned for the incorrect claims that without HDMI 1.3 a new Hi-def DVD player (of either format) could not output new multichannel lossless audio formats (Dolby True-HD, DTS-HD, etc) to an A/V receiver.</p>

<p>Actually it could, the player converts the new audio format as LPCM and send it to the A/V receiver that way even thru a non-1.3 HDMI connector. For that to happen, the player must have the proper audio decoder for the lossless audio format read from the disc. Version 1.3 would permit the encoded audio to be streamed out as is read from the disc, for an external decoder to do the job (future A/V Receivers); there are some pros and cons on doing this. This subject is covered in depth in the following article:</p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php">http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php</a></p>

<p><br />
<h2>The Versions</h2><br />
Let us start with some basic bullets about the HDMI specs on each version:</p>

<p><b>HDMI 1.0</b> (Dec 2002)<br />
-Max Video Performance<br />
1080p @ 60Hz refresh rate, or UXGA (PC format)<br />
24 bit RGB/36 bit YCrCb color depth</p>

<p>-Max Audio Performance<br />
8 channels uncompressed digital audio @ 192kHz, 24 bits per sample<br />
Support for all existing Dolby &amp; DTS compressed formats</p>

<p><b>Time Line:</b><table class="bare" cellspacing="0"><tr><td class="grid">December 2002</td><td class="grid">June 2004</td><td class="grid">August 2005</td><td class="grid">December 2005</td></tr><tr><td class="grid"><b>HDMI 1.0</b></td><td class="grid"><b>HDMI 1.1</b></td><td class="grid"><b>HDMI 1.2</b></td><td class="grid"><b>HDMI 1.2a</b></td></tr><tr><td class="grid">Initial specification</td><td class="grid">Added support for DVD-Audio</td><td class="grid">Added support for SACD</td><td class="grid">CEC Functionality fully specified</td></tr><tr><td class="grid">&nbsp;</td><td class="grid">Improvements to compatibility testing</td><td class="grid">Permits use of RGB color space for monitor applications</td><td class="grid">Testing required for specific cable lengths</td></tr><tr><td class="grid">&nbsp;</td><td class="grid">&nbsp;</td><td class="grid">Supports low-voltage (AC-coupled sources) in PCs</td><td class="grid">Certified Connector List -required to pass ATC testing</td></tr></table></p>

<p><b>1.3 Next Gen HDMI Performance</b><br />
-Maximum bandwidth increased from 165 MHz (4.95Gbps) to 340 MHz (10.2Gbps)</p>

<p>-Technical foundation established for future bandwidth increases</p>

<p>-Support for increased refresh rate and next generation displays<br />
e.g. 1080p @ 60Hz with 36 bit RGB, or 1080p @ 90Hz refresh rate WQXGA displays (1440p)</p>

<p>-Added support for Deep Color for increased color bit-depth 30-, 36-, and 48-bit RGB/YCbCr</p>

<p>-Current 24 bit color enables ~17 million colors. Deep color enables billions of colors. Helps eliminate on-screen effects such as color banding</p>

<p>-Added support for next generation "xvYCC" color standard, will allow the display of any viewable color (1.8X as many colors as existing standard)</p>

<p>-Version 1.3 ensures highest possible signal resolutions from next generation video sources: Blu-Ray and HD-DVD, PS3, HDMI-equipped PCs</p>

<p>-Maximum Audio Performance<br />
Version 1.3 adds support for future Dolby TrueHD &amp; DTS-HD Master audio formats (Blu-ray/HD-DVD). Lossless compression formats bring a similar digital surround experience as original theater movies.</p>

<p>-Automatic Lip Sync timing compensation to enable automatic correction for most common audio/video sync issues</p>

<p>-Mini connector for camcorders, digital-still cameras, etc.</p>

<p><br />
The latest HDMI specification can be downloaded at no cost by visiting <a href="/cgi-bin/ntlinktrack.cgi?http://www.hdmi.org/">www.hdmi.org</a></p>

<p><br />
Stay tuned for Part 2, coming soon to a HDTV theater near you, the HDTV Magazine.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>July 25, 2006 09:57 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 404
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
 				AND entry_id <> 404
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/07/hdmi_-_a_digital_interface_solution.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
