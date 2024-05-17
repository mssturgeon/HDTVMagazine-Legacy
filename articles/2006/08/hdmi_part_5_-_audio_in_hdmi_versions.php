<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 414";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 414 AND placement_is_primary = 1";
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
	<meta name="keywords" content="multi channel, audio formats, channel audio, dolby digital, def dvd, audio, hdmi, HDMI, Dolby, dolby, DVD, formats, receiver, dvd, channel, Audio, bit, dts, DTS, multi, digital, players, device, player, connection" />
	<meta name="description" content="&lt;img src=&quot;http://www.hdtvmagazine.com/images/hdmi_200.gif&quot; alt=&quot;HDMI&quot; align=&quot;right&quot;&gt;While the multichannel audio industry keeps creating more formats for newer equipment, it also creates the need of compatibility with existing equipment. And being the digital connectivity solution that the industry says it is, HDMI has to meet the challenge of that &quot;evolution&quot;.

When HDMI 1.1 came out, it added to the spec a new packet to carry some DVD-Audio content protection-related data. All audio capabilities of DVD-Audio were part of 1.0 but the CPPM/CPRM license (used for DVD-Audio encrypted disks) required some additional data to be transmitted.

Then, when HDMI 1.2 came out..." />
	<title>HDTV Magazine Articles - HDMI Part 5 - Audio in HDMI Versions</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdmi_part_5_-_audio_in_hdmi_versions';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HDMI Part 5 - Audio in HDMI Versions'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_5_-_audio_in_hdmi_versions.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDMI Part 5 - Audio in HDMI Versions</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>August  8, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_5_-_audio_in_hdmi_versions.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_5_-_audio_in_hdmi_versions.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_5_-_audio_in_hdmi_versions.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_5_-_audio_in_hdmi_versions.php&amp;phase=2&amp;title=HDMI%20Part%205%20-%20Audio%20in%20HDMI%20Versions&amp;bodytext=%3Cimg%20src%3D%22http%3A%2F%2Fwww.hdtvmagazine.com%2Fimages%2Fhdmi_200.gif%22%20alt%3D%22HDMI%22%20align%3D%22right%22%3EWhile%20the%20multichannel%20audio%20industry%20keeps%20creating%20more%20formats%20for%20newer%20equipment%2C%20it%20also%20creates%20the%20need%20of%20compatibility%20with%20existing%20equipment.%20And%20being%20the%20digital%20connectivity%20solution%20that%20the%20industry%20says%20it%20is%2C%20HDMI%20has%20to%20meet%20the%20challenge%20of%20that%20%22evolution%22.%0A%0AWhen%20HDMI%201.1%20came%20out%2C%20it%20added%20to%20the%20spec%20a%20new%20packet%20to%20carry%20some%20DVD-Audio%20content%20protection-related%20data.%20All%20audio%20capabilities%20of%20DVD-Audio%20were%20part%20of%201.0%20but%20the%20CPPM%2FCPRM%20license%20%28used%20for%20DVD-Audio%20encrypted%20disks%29%20required%20some%20additional%20data%20to%20be%20transmitted.%0A%0AThen%2C%20when%20HDMI%201.2%20came%20out...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img src="/images/hdmi_200.gif" alt="HDMI" align="left">While the multichannel audio industry keeps creating more formats for newer equipment, it also creates the need of compatibility with existing equipment. And being the digital connectivity solution that the industry says it is, HDMI has to meet the challenge of that "evolution".</p>

<p>When HDMI 1.1 came out, it added to the spec a new packet to carry some DVD-Audio content protection-related data. All audio capabilities of DVD-Audio were part of 1.0 but the CPPM/CPRM license (used for DVD-Audio encrypted disks) required some additional data to be transmitted.</p>

<p>Then, when HDMI 1.2 came out, the primary audio feature added to the spec was "One Bit Audio" which is a generic name for DSD, the audio format used in SACD (Super Audio CD).</p>

<p>Later, HDMI 1.2a was released primarily to put in place all of the specs and test methods to allow a full implementation of CEC (Consumer Electronic Control), allowing one touch control over the entire system. Before HDMI 1.2a it was possible to do a CEC implementation, but since there was no complete test spec no vendor did.</p>

<p>According to Joseph Lee, Director of Marketing of Simplay Labs LLC, the HDMI specification requires at minimum that 2 channel PCM audio be supported over the HDMI interface, all other audio formats are optional. However, a source device supporting this bare minimum on its HDMI output would be unusual, as the video and audio capabilities that most HD devices supporting HDMI are the same or better than the capabilities available from the older analog &amp; S/PDIF outputs.</p>

<p>Since HDMI is an open industry standard, the specification does not mandate specific high-resolution formats (e.g. 720p, 1080i, etc.) to be available in all logo' d devices, but leaves it up to manufacturers to choose in order to differentiate their products. There are no technical reasons why any HDMI host devices would not support the best audio & video capabilities on the HDMI output; however, manufacturers may choose not to implement some formats in order to build lower cost devices.</p>

<p>It is possible that some devices might be able to gain the capability to support digital surround sound formats on the HDMI output with a firmware upgrade, but this depends on the hardware architecture and the manufacturer's decision to support such an upgrade.</p>

<p><br />
<h2>Multi-channel Audio does not like my HDMI connection!</h2></p>

<p>Not too long ago, some articles claimed that HDMI was not implemented by some manufacturers as a full multi-channel connection. The confusion came from the fact that the majority of first-generation HDMI suited devices were TVs with only two-channel stereo and had no use for the full multi-channel signal.</p>

<p>However, most other equipment, from DVD players to A/V receivers, switchers, etc, should be capable to receive, process, switch, or send the full multi-channel audio content across HDMI.</p>

<p>HDMI is a two-way communication between the source device and the receiving device by which the receiving device tells the source about its multi-channel capabilities. The source device can then send a matching signal, such as two-channel stereo to a TV, or 5.1 DD channel to a 5.1 A/V receiver. In other words, the source device adapts to the receiving device when sending the signal.</p>

<p>In the case of an A/V receiver receiving the signal from a 5.1 DD DVD player, both ends of the connection recognize the need to maintain the 5.1, but the receiver might redirect the signal to a TV that needs only L/R channels, for which the output of the receiver adapts on only that output jack by down-mixing the DD stream.</p>

<p>Regarding the newer hi-bit multi-channel audio codecs from Dolby and DTS, the consumer should verify that the HDMI transmitter/receiver chip installed in both ends of the HDMI link is actually capable to transport the multi-channel audio you plan to play. For example, one legacy application could limit itself to transport the typical Dolby Digital 5.1, another consumer might require transporting SACD, a feature implemented on chips complying with the current HDMI specification version 1.2.</p>

<p>Earlier transmitter/receiver HDMI chips could have been manufactured based on specification versions not suited for newer audio formats, and one could not expect that old chips support newer functionality, such as SACD, DTS-HD, Dolby TrueHD, Dolby Digital Plus, etc. For more information about the applicability and connectivity of hi-bit audio formats , please review the following article I recently wrote about Multi-channel Audio for HD:</p>

<p><a href="http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php">http://www.hdtvmagazine.com/articles/2006/04/multi-channel_a.php</a></p>

<p><br />
<h2>Hi-bit Audio Application to Hi-def DVD Formats</h2></p>

<p>In September 23, 2004, Dolby Laboratories announced that the DVD Forum decided to include Dolby Digital Plus and MLP Lossless, the core audio technology behind multichannel DVD-Audio, as mandatory audio standards for HD DVD. Later, Dolby TrueHD was also selected as mandatory audio format for HD DVD. However, both Dolby formats were selected as optional audio formats for Blu-ray players. DTS-HD was declared as optional for the players of both Hi-def DVD formats.</p>

<p>In other words, Blu-ray approved as optional the 3 hi-bit audio formats (Dolby Digital Plus, Dolby TrueHD, and DTS-HD), the only mandatory codecs for Blu-ray are the legacy 5.1 DD and DTS.</p>

<p>According to Silicon Image, the HDMI transport is able to handle 24Mbps of audio speed, suitable for any of the proposed audio formats from either disc format, including DTS HD Master Audio. However, the version 1.3 HDMI specification would enable the players to output those audio formats over HDMI for them to be decoded externally, by a future A/V receiver capable to do so, for example, if one prefers so; the protocols and specifications were finalized and available in June 2006. Dolby is working closely with Silicon Image to ensure transmission of Dolby Digital Plus and TrueHD signals on HDMI v. 1.3.</p>

<p>As an alternative, HD players with internal hi-bit-rate decoders are expected to also have 6.1 or 7.1 analog outputs that could support the hi-bit-rate and be connected to receivers with 6.1 or 7.1 channel analog inputs.</p>

<table class="grid" cellspacing="0" align="center"><tr><td class="grid"><img src="/images/articles/multichannel-connect.gif" alt="Connection via Multichannel Analog Inputs (graph courtesy of Dolby Laboratories)"></td></tr><tr><td class="grid" style="text-align:center">Connection via Multichannel Analog Inputs (graph courtesy of Dolby Laboratories)</td></tr></table>

<p><br />
<h2>Hi Def DVD, Using HDMI for Audio</h2></p>

<p>In September 2005, Dolby announced that A/V receivers capable of processing PCM over their HDMI 1.1 inputs should also be able to have sufficient bandwidth to accept the HD video and the PCM multi-channel audio decoded by the Hi-def DVD player.</p>

<p>Any HDMI suited A/V receiver should be capable to input the PCM and reproduce the higher bandwidth of the soundtracks. Initially, it was believed that those HDMI 1.1 suited A/V receivers would have to use analog cables from the multi-channel audio connectors (as with DVD-Audio), and wait until specification version 1.3 of HDMI be completed (and eventually change to a 1.3 compliant A/V receiver).</p>

<p>According to Dolby, there should be no need to replace a receiver suited with HDMI 1.1 to get the benefit of the higher-bit audio formats. However, when using the latest HDMI version 1.3 from player to receiver, the decoding would not have to happen in the player, the connection would stream the native mandatory and optional audio formats to the HDMI 1.3 suited A/V receiver, which would perform the decoding job.</p>

<table class="grid" cellspacing="0" align="center"><tr><td class="grid"><img src="/images/articles/pcm-connect.gif" alt="Connection via Next-Generation HDMI (graph courtesy of Dolby Laboratories)"></td></tr><tr><td class="grid" style="text-align:center">Connection via Next-Generation HDMI (graph courtesy of Dolby Laboratories)</td></tr></table>

<p><br />
Reportedly, DTS intends to suit players as well as receivers with their decoders, Dolby was quoted as concentrating initially on players.</p>

<p>Hi-definition DVD disc players are expected to support Internet-streamed audio content (such as director's comments) while playing the movie, and they have to internally mix the various audio components (soundtrack, Internet, PCM sounds, etc) before converting the final audio mix to individual PCM channels to be output over the HDMI connection.</p>

<p>The newer hi-bit formats, Dolby Digital Plus lossy, Dolby TrueHD lossless, and DTS HD lossless (previously named DTS++ lossless, and now extended to Master Audio 24 Mbps), are much faster than the supported speed of typical digital coaxial connections (S/PDIF) used for the current legacy Dolby Digital and DTS multichannel audio formats, however, those legacy connections would still transport the down-converted legacy versions (derived from the hi-bit) produced by Hi-def DVD players.</p>

<p><br />
<h2>Lip Sync Feature of 1.3, how would be implemented?</h2></p>

<p>There was a question recently by one of the Magazine readers interested to know if both pieces of equipment, the source and the receiving device, would need to be 1.3 capable in order for the "Lip Sync" feature to work. I contacted Leslie Chard, President of HDMI Licensing LLC, he had the courtesy to provide details as follows, and I quote:</p>

<p>"The 1.3 lip sync correction functionality is required on the device that creates the lip sync problem (typically a display - which has a latency between audio/video processing because of the more demanding requirements of video processing), and a device that can correct the lip sync delay (the initial implementations of this will be in a receiver, but in the future this functionality will be in DVD players, and most other CE devices.) The reports that we are getting from manufacturers indicate that this function is very popular and will be widely implemented."</p>

<p><br />
<h2>Upgrade? For Audio? Again?</h2></p>

<p>We have said that if an existing receiver does not have HDMI inputs it can still use the multichannel analog connections (6 to 8 RCA type of connections) until is time for the upgrade; remember the convenient DVD-Audio mess of wires?</p>

<p>In selecting a Hi-def DVD player of any format, one factor of choosing one model over the other could be the implementation of the Hi-bit multichannel codecs that are optional (Dolby TrueHD, Dolby Digital Plus, DTS HD, depending on the format).</p>

<p>Even when not having the latest A/V receiver that could decode the Hi-bit formats itself using the HDMI 1.3 connection, if a consumer is interested in a system to reproduce the optional DTS-HD for example, that consumer would be making a better investment by choosing a Hi-def player that decodes DTS-HD by itself. The existing receiver, using the alternative connections above, would be spared from an unneeded upgrade just for that purpose.</p>

<p>In summary, there will be a variety of backward compatibility connectivity options to allow consumers to still be able to use the existing audio equipment at their current multi-channel audio capabilities when playing back the new audio formats of High Definition DVD discs/players, but there will be enough incentive for upgrades.</p>

<p>Upgrading to an A/V receiver suited with HDMI 1.1 would bring the full benefit of the lossless audio formats transporting the channels digitally as PCM, and if the upgrade could be done to HDMI 1.3 connectivity it would open the possibility to do hi-bit decoding on the receiver, giving the consumer the option of doing the audio decoding in the A/V receiver or the player, which ever sounds best for the consumer, and perhaps been able to decode a hi-bit codec missing in the player but available in the receiver.</p>

<p>In that scenario, the player would just stream out over HDMI the un-decoded hi-bit multichannel signal for the A/V receiver to decode, expanding the flexibility of the audio part of the system. However, in some specific conditions, using the HDMI 1.3 connection for streamed audio (not PCM) might disallow the mixing of the additional audio features of Hi-def DVD over the soundtrack, unless the player is suited with an optional "encoder" of such signals over that output.</p>

<p><br />
Stay tuned for Part 6 "1080p Support"</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>August  8, 2006 05:19 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 414
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
 				AND entry_id <> 414
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/08/hdmi_part_5_-_audio_in_hdmi_versions.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
