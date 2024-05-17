<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 724";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 724 AND placement_is_primary = 1";
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
	<meta name="keywords" content="iptv part, real time, video codec, data bit, video output, iptv, IPTV, video, MatrixStream, matrixstream, STB, mpeg, MPEG, stb, part, viewing, Part, vod, content, VOD, output, company, IMX, imx, HDTV" />
	<meta name="description" content="When using standard MPEG-2 compression, an HD channel requires about 19 Mbps of bandwidth to be transmitted. If using DSL or cable modem, although it is considered hi-speed for typical Internet services, it currently has the capacity to transmit only a small fraction of what raw HDTV requires.

Even when re-compressing the 19 Mbps with more efficient compression algorithms like MPEG-4 or VC1, and even when using additional transmission-saving techniques to fit HDTV content into those typical hi-speed Internet services, the approach would be a challenge, not to mention that after making use of this bandwidth for HDTV there will be little or no headroom left for Internet downloads of music, files, photos, etc.

Additionally, it becomes less feasible to..." />
	<title>HDTV Magazine Articles - IPTV Part 3 - The Methods and a Working Technology</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/iptv_part_3_-_the_methods_and_a_working_technology';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('IPTV Part 3 - The Methods and a Working Technology'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">IPTV Part 3 - The Methods and a Working Technology</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>October  2, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php&amp;phase=2&amp;title=IPTV%20Part%203%20-%20The%20Methods%20and%20a%20Working%20Technology&amp;bodytext=When%20using%20standard%20MPEG-2%20compression%2C%20an%20HD%20channel%20requires%20about%2019%20Mbps%20of%20bandwidth%20to%20be%20transmitted.%20If%20using%20DSL%20or%20cable%20modem%2C%20although%20it%20is%20considered%20hi-speed%20for%20typical%20Internet%20services%2C%20it%20currently%20has%20the%20capacity%20to%20transmit%20only%20a%20small%20fraction%20of%20what%20raw%20HDTV%20requires.%0A%0AEven%20when%20re-compressing%20the%2019%20Mbps%20with%20more%20efficient%20compression%20algorithms%20like%20MPEG-4%20or%20VC1%2C%20and%20even%20when%20using%20additional%20transmission-saving%20techniques%20to%20fit%20HDTV%20content%20into%20those%20typical%20hi-speed%20Internet%20services%2C%20the%20approach%20would%20be%20a%20challenge%2C%20not%20to%20mention%20that%20after%20making%20use%20of%20this%20bandwidth%20for%20HDTV%20there%20will%20be%20little%20or%20no%20headroom%20left%20for%20Internet%20downloads%20of%20music%2C%20files%2C%20photos%2C%20etc.%0A%0AAdditionally%2C%20it%20becomes%20less%20feasible%20to...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV Part 1 - Read the Fine Print</a></li>
<li><a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_5_-_additional_implementations.php">IPTV Part 5 - Additional Implementations</a></li>
<li><a href="/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php">IPTV Part 6 - More Implementations and Final Thoughts</a></li>
</ul></div>
<br />

<p><B>Different Methods of HDTV Over IP</B></p>

<p>When using standard MPEG-2 compression, an HD channel requires about 19 Mbps of bandwidth to be transmitted. If using DSL or cable modem, although it is considered hi-speed for typical Internet services, it currently has the capacity to transmit only a small fraction of what raw HDTV requires.</p>

<p>Even when re-compressing the 19 Mbps with more efficient compression algorithms like MPEG-4 or VC1, and even when using additional transmission-saving techniques to fit HDTV content into those typical hi-speed Internet services, the approach would be a challenge, not to mention that after making use of this bandwidth for HDTV there will be little or no headroom left for Internet downloads of music, files, photos, etc.</p>

<p>Additionally, it becomes less feasible to consider accommodating additional parallel real-time HD channels to satisfy the individual viewing needs of a typical home with several TVs.</p>

<p>Some IPTV advertising campaigns say, "We could download any HD program of your choice into the DVR for later viewing", the download could happen while you sleep so the downloading speed would not need to be as fast as the viewing speed from the DVR.</p>

<p>Others say, "We could send the program for real-time viewing" which generally means one selection from a group of options, like a VOD service.</p>

<p>Other IPTV service providers claim to have found a way to send several HD feeds throughout various rooms in the home.</p>

<p>Others advertise having hundreds of channels on the line up, but fail to elaborate on the viewing restrictions, compression artifacts, freeze ups, single TV per home limitations, etc.</p>

<p>IPTV service providers like AT&T and Verizon consider themselves to have an advantage over cable companies because their IPTV services only require just enough bandwidth to send the selected channel.</p>

<p>This means they do not need to send out all the 150 parallel channels like cable and satellite. More specifically, the channel tuning selection is not done the traditional way as with terrestrial, cable, etc, where a viewer chooses from a wide selection of parallel channels arriving to the STB and selects only one from the multi-channel stream.</p>

<p>When using IPTV, you select and request delivery of the specific program to your STB from the line up. Such delivery could be viewed in real-time or be downloaded to a DVR for later viewing, depending of the service and installed hardware.</p>

<p>IPTV is being implemented in different flavors, and part of the reason for the variation is that while some neighborhoods have very limited Internet speed, others have been provisioned with very fast fiber optic networks.</p>

<p><br />
<B>Current/Planned IPTV Market Solutions</B></p>

<p>A couple of years ago I was contacted by a company that developed an HD-IPTV system. Using this solution, consumers would purchase client STBs for PCs and stand-alone TVs and receive IPTV content even at 1080p quality, as claimed by the company.</p>

<p>MatrixStream is the name of that company, founded in 1999 and headquartered in Vancouver, British Columbia. I included their launched HD 1080p server/client IPTV products on last year's annual HDTV Technology report (March 2006, 2006 HDTV Technology Report).</p>

<p>The interesting part was that the company also offered hardware to enable "anyone" to become a content distributor via IP from a server to clients in a network.</p>

<p>The company adopted MPEG-4 Part 10/H.264 compression which halves the stream requirements to transmit via IP for typical HDTV content compressed with MGEG-2. MatrixStream also implemented techniques to optimize the transport over the Internet by using proprietary buffering and error-correction features that compensate for Internet bottlenecks.</p>

<p>MatrixStream claims they can transmit a DVD-quality TV signal requiring only 1.5 Mbps and a high-definition 1080p signal requiring only 2.5 Mbps, both within the limitations of typical DSL and cable-modem hi-speed connections.</p>

<p><br />
<B>MatrixStream IPTV Technologies</B></p>

<p>Over a year ago the company introduced the world's first VOD and IPTV HD-STB using H.264 AVC (advance video codec) adapted for 1080p, with 80GB of HDD, to receive SD and HD IPTV signals over broadband.</p>

<p>The STB allows high bandwidth users (1.5 Mbps+) to view videos in real time via live streaming. For users with lower bandwidth, the IMX Set Top Box (STB) has the proprietary video preload feature that preloads videos to the STB cache prior to viewing.</p>

<p><img src="/images/products/matrixstream.jpg" alt="Matrixstream IMX 1020HD IPTV HD STB" align="right" /><u>IMX 1020HD IPTV HD STB</u><br />
Available for trials since Jan 06, TTM 1Q06, supports HD 720P, 1080i and 1080P formats, H.264/MPEG 4 Part 10, streaming video, download and push VOD, 1080p over HDMI, component analog able to output 1080i subjected to downrez if the content protection requires it, HDCP over HDMI is activated depending on the content provider contract and STB (the boxes are offered world wide so it varies by location).</p>

<p>According to the company, the IPTV signal will always be protected by encryption. It is up to the service provider to decide if they want to turn HDCP on or off depending on the contract agreement for content they have made for IPTV delivery. Usually a customer will get the set-top box from the service provider directly, however, some service providers might choose to provide it over retail, i.e.: Best Buy.</p>

<p><u>IMX 1000 IPTV STB</u><br />
Designed to support Windows Media/VC-1 video codec, fully supports push VOD, download VOD, and streaming VOD in Windows Media format over the Internet.</p>

<p><u>IMX 1100 PC Player</u><br />
Available since Jan 06 , originally from the movie99.tv website. It offered over 300 free channels from around the world and 150 free DVD and HD quality movie clips.</p>

<p><br />
<u>Deployment Diagram</u><br />
The following is an example of how an IMX 1000 STB is deployed in a VOD environment. Each STB is connected to a TV through standard RCA output, S-Video output, component video output, or DVI output. Each STB fully supports Dolby Digital 5.1 Surround Sound via the optical PCM output.</p>

<p>Source: <a href="/cgi-bin/ntlinktrack.cgi?http://www.matrixstream.com/" target="_blank">MatrixStream</a></p>

<p><img src="/images/articles/matrixstream-vod-solution.jpg" alt="MatrixStream VOD Solution" /></p>

<p>According to the company, "MatrixStream's solution is generally marketed to broadband providers seeking an opportunity to increase ROI by deploying video over their networks. On the back-end, broadband providers have access to one of the most cost-effective, scaleable VOD systems available, complete with billing, management, subscriber management, channel management, and digital rights management. MatrixStream's solution supports industry standard video codecs like MPEG4, VC-1, and H.264 and is capable of supporting all future video codecs".</p>

<p>"MatrixStream's IPTV solution is automatically programmed with features currently available on DVDs, including interactive menus, subtitles, multiple audio tracks and video chapters."</p>

<ul><li>High Definition Video Support - MatrixStream's video viewing clients display DVD and HD videos.</li><li>Video Content Security - MatrixStream offers extensive security measures, including built-in Microsoft DRM (Digital Rights Management) support with the option to add any 3rd party DRM system. Dynamic watermarking management technology is also utilized to protect content from piracy.</li><li>Advanced Video Codec Support - MatrixStream fully supports multiple industry standard advance codecs such as H.264, MPEG 4 Part 10, and VC-1. MatrixStream designed its IPTV system to support all existing and future subsequent video codecs.</li><li>Dynamic Advertising Module - MatrixStream allows IPTV operators/broadband providers complete control over dynamic ad insertion, targeting viewers based on personal preferences and interests.</li><li>Flexible Viewing Options - MatrixStream supports video viewing on both PC and the IP STB clients. STB clients can be remotely upgraded with new releases and additional options. MatrixStream viewing clients can also be ported to third party platforms and OEM STBs.</li></ul>

<p><u>Video Formats</u><br />
NTSC/PAL composite<br />
NTSC/PAL s-video<br />
Analog YPbPr / RGB<br />
150 MHz YCbCr / RGB digital video output interface<br />
- 8-bit 4:2:2 YCbCr data<br />
- 16-bit 4:2:2 YCbCr data<br />
- 24-bit 4:4:4 YCbCr data<br />
- 24-bit RGB data (888)<br />
- BT.601, BT.656, or VIP 2.0, "video valid" output signal<br />
- Master or slave timing</p>

<p><u>Resolution / Frame Refresh Rates</u><br />
704/720 x 480i 30Hz<br />
704/720 x 480p 60Hz<br />
704/720 x 576i 25Hz<br />
704/720 x 576p 50Hz<br />
1280x720p 50/60Hz<br />
1366x768p 50/60Hz<br />
1024x1024p 50/60Hz<br />
1920 x 1080i 25/30Hz<br />
1920 x 1080p 50/60Hz</p>

<p><u>Audio Formats</u><br />
- 16-bit linear PCM with HDCD support<br />
- MPEG-1 and MPEG-2 Layers I, II and III (MP3) 2.0<br />
- MPEG-2 BC multi-channel Layers I, II and III 5.1<br />
- MPEG-2 and MPEG-4 AAC-LC 2.0<br />
- MPEG-2 and MPEG-4 HE-AAC 2.0<br />
- MPEG-4 SBAC 2.0<br />
- Dolby Digital 5.1<br />
- DTS 5.1</p>

<p><u>Back panel connections</u><br />
- WMA9@L3 2.0, WMA9 Lossless 2.0, WMA9 Pro@M2 5.1, LAN: 10/100 Base-T, RJ 45<br />
- Connectivity: Two USB 2.0 ports<br />
- Video: HDMI/DVI, S-Video, RCA composite, Y/Pb/Pr<br />
- Audio: S/PDIF, Left/Right channel audio output</p>

<p><img src="/images/articles/matrixstream-player-requirements.jpg" alt="MatrixStream Player Requirements" /></p>

<p>In September 2006, MatrixStream released a new HD IPTV package featuring video on demand (VOD), an IPTV basic IMX500 middleware server, an IMX 2410 XMS streaming server, and an IMX 4010 video encoder, capable of handling up to 500 concurrent users, <$70,000, XMS streaming technology, H.264 compliant, fully integrated, end-to-end solution, including billing management, subscriber management, channel management and digital rights management.</p>

<p>According to MatrixStream, the package can be implemented in a very short time and with minimum cost of deployment. The system performs over any broadband network with no Quality of Service (QoS) requirements.</p>

<p>In the next article, I will analyze several of these services and highlight the pros and cons.</p>

<p>Next Article: <a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>October  2, 2007 07:51 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 724
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
 				AND entry_id <> 724
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
