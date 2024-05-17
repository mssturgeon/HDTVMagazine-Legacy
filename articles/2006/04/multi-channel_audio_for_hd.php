<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 367";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 367 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dolby digital, dolby truehd, digital plus, next generation, blu ray, audio, Dolby, dolby, digital, bit, Digital, DVD, dvd, player, formats, truehd, dts, DTS, TrueHD, receiver, channel, hdmi, HDMI, lossless, players" />
	<meta name="description" content="We had not planned on releasing this for some time, but the recent questions about HD DVD audio prompted Rodolfo and I to get this out to the public while it was most useful.  I hope you find it so.

Topics covered include:
- Hi-bit Dolby Digital Formats - Connectivity
- Legacy Discrete Surround Audio Formats for Hi Def DVD
- Hi-bit Surround Audio Formats - Summary
- Hi-bit Audio Application to Hi-def DVD Formats
- Analysis" />
	<title>HDTV Magazine Articles - Multi-channel Audio for HD</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/multi-channel_audio_for_hd';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Multi-channel Audio for HD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Multi-channel Audio for HD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>April 21, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php&amp;phase=2&amp;title=Multi-channel%20Audio%20for%20HD&amp;bodytext=We%20had%20not%20planned%20on%20releasing%20this%20for%20some%20time%2C%20but%20the%20recent%20questions%20about%20HD%20DVD%20audio%20prompted%20Rodolfo%20and%20I%20to%20get%20this%20out%20to%20the%20public%20while%20it%20was%20most%20useful.%20%20I%20hope%20you%20find%20it%20so.%0A%0ATopics%20covered%20include%3A%0A-%20Hi-bit%20Dolby%20Digital%20Formats%20-%20Connectivity%0A-%20Legacy%20Discrete%20Surround%20Audio%20Formats%20for%20Hi%20Def%20DVD%0A-%20Hi-bit%20Surround%20Audio%20Formats%20-%20Summary%0A-%20Hi-bit%20Audio%20Application%20to%20Hi-def%20DVD%20Formats%0A-%20Analysis&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<blockquote>This is an excerpt from the <b>HDTV Technology Review 2006 Report</b> by Rodolfo La Maestra. We had not planned on releasing this for some time, but the recent questions about HD DVD audio prompted Rodolfo and I to get this out to the public while it was most useful.  I hope you find it so.<br>
<br>
If you are interested in the full version of this report, it is currently available from the <a href="/reports/hdtv-technology-review.php">HDTV Technology Review</a> page.<br>
<br>
The other parts in the series are:<br>
Part 1: <a href="/articles/2006/03/hdtv_technology_review_part_1_introduction.php">HDTV Technology Review, Part 1: Introduction</a><br>
Part 2: <a href="/articles/2006/04/1080p_into_hdtv_displays.php">1080p into HDTV Displays</a><br>
</blockquote>

<p><br><br />
<br><br />
<span style="font-size:12pt"><center><strong>Multi-channel Audio for HD</strong></center></span></p>

<p><br />
<h2>Hi-bit Dolby Digital Formats - Connectivity</h2><br />
In September 05, Dolby Laboratories announced its newest lossless audio multichannel format, Dolby<sup>&reg;</sup> TrueHD, for the high-definition optical disc formats, Blu-ray and HD-DVD.  The new format claims to be equaled bit-for-bit in performance to the highest-resolution studio masters currently available.</p>

<p>As covered in detail further down, Dolby Digital Plus and Dolby TrueHD have been approved as mandatory audio codecs in the HD DVD format (all players must be able to decode it), while they are optional in the Blu-ray disc format.  Dolby Digital is mandatory in both disc formats.</p>

<p>According to Dolby: "Dolby TrueHD builds upon the proven foundation of MLP Lossless&trade; by incorporating higher bit rates, additional channels, enhanced stereo mix support, and extensive metadata functionality, including dynamic range control and dialogue normalization.  Enabling recordings that are bit-for-bit identical to studio masters, MLP Lossless was first introduced in DVD-Audio, and has since become the leading multichannel lossless audio format.  In addition, Dolby TrueHD provides support for all of the new speaker locations designated by the Society of Motion Picture and Television Engineers (SMPTE) for digital cinema applications (RP 226)."</p>

<p>New delivery formats that can support at least a 2 Mbps bit rate for audio are potential software candidates.  The first applications to adopt Dolby TrueHD are Blu-ray Disc and HD DVD, as these can support up to 18 Mbps for audio.</p>

<p>Next-generation Hi-def players are being designed to include features like interactivity and audio mixing which require the audio to be decoded in the player instead of the A/V receiver.  A Dolby TrueHD multichannel decoder in the player will be the only way to ensure that listeners will hear the full quality of the wide range of audio capabilities.</p>

<p>Unlike perceptual or lossy data reduction, lossless coding does not alter the final decoded signal in any way, but merely "packs" the audio data more efficiently into a smaller data rate for storage or transmission.  The lossless version always sounds like the source.  The lossy version may sound like the source, but this is not guaranteed.  The perceived quality of a lossy audio format depends on many factors, including the nature of the source material, the compression efficiency of the codec, the delivery bit rate chosen, the quality of the playback hardware, and the listening environment.</p>

<p>Dolby TrueHD also provides unique support for stereo playback, either via a programmable downmix or a wholly separate stereo mix, ensuring that surround content creators can deliver the companion stereo mix exactly as they intend, without compromise.  Dolby TrueHD for next-generation high-definition media delivers sampling frequencies from 48 to 192 kHz and word lengths from 16 to 24 bits.  The sample rate and word length for Dolby TrueHD content will always be the same for all channels.</p>

<p>The bit rate needed to deliver a Dolby TrueHD lossless track depends on the characteristics of the source material, bit depth, and sampling frequency.  Dolby TrueHD for next-generation high-definition media can operate at data rates up to 18 Mbps.  All new players incorporating Dolby TrueHD technology will support this maximum data rate.</p>

<p>Dolby TrueHD for next-generation high definition media supports up to eight channels of audio, and offers expandability to accommodate more channels in the future while retaining compatibility with all Dolby TrueHD decoders.  The Dolby TrueHD stream is structured so that a player only needs to decode the number of channels it needs.  This ensures that a single Dolby TrueHD stream can be used to deliver a two-, six-, or eight-channel presentation with precise control over the playback defined by the content producer.</p>

<p>Dolby TrueHD is designed to offer comprehensive metadata functionality similar to that found in Dolby Digital and Dolby Digital Plus.  This includes down-mixes that are defined by the content producer, dynamic range compression for late-night listening, and dialogue normalization to ensure consistent playback loudness between different content.  For future content featuring discrete 7.1-channel playback, Dolby TrueHD also supports multiple 7.1 configurations.</p>

<p>The following highlights the various multichannel audio connections between near future High Definition DVD players and A/V Receivers for the Dolby formats.</p>

<p><em>Disclaimer: The information of connectivity and graphs included below are provided courtesy of Dolby Laboratories with permission.</em></p>

<p>In HD disc players, the audio will be handled in much the same fashion.  Soundtracks decoded from the disc, as well as audio elements streamed or downloaded from an Internet connection or generated internally in the player will be decoded in the player as digital PCM signals.  PCM is the format players use to perform all internal audio processing operations, including mixing.  In the mixing stage, streaming commentary, button sounds, and other non-disc-audio will be mixed with the native 5.1 or 7.1 soundtrack from the disc.  The result will be the complete audio presentation as intended by the content maker.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image364.gif" alt="Next-Generation Six-Channel Optical Player with Dolby Digital Output Encoder" align=center></td></tr><tr><td align="center">Figure 1 - Next-Generation Six-Channel Optical Player with Dolby Digital Output Encoder</td></tr></table>

<p>The implications of this decoding within the player are significant.  New features can be created for a given title long after the discs have shipped.  More importantly, the fact that players will be mixing the audio internally means that it will no longer be possible (or necessary) to output raw audio bitstreams from the player as is typical with DVD-Video.  As a result, consumers can no longer assume that every player will work with every A/V receiver.</p>

<p>Two methods already exist for reproducing the high-resolution soundtracks of next generation optical formats through your A/V receiver or audio processor.</p>

<p><br />
<u>Single-Cable Digital Connection</u></p>

<p>Increasingly, A/V processors and receivers are being equipped with IEEE 1394 (FireWire<sup>&reg;</sup>) or HDMI connections, capable of transporting up to eight channels of 24-bit/96 kHz PCM audio content. If your A/V receiver is equipped with this type of next generation connection, you should look for a similarly furnished next-generation optical media player.  By this method of connection, the mixed PCM signal is transported from the HD player to your A/V receiver, where digital signal processing and bass management can be easily effected.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image366.gif" alt="Connection via Current HDMI" align=center></td></tr><tr><td align="center">Figure 2 - Connection via Current HDMI</td></tr></table>

<p><br />
<u>Multichannel Analog Connection</u></p>

<p>A next-generation optical player may also include line-level audio outputs sourced from the multichannel mixed PCM signals passed through digital-to-analog converters.  The advent of SACD and DVD-Audio in recent years has led to the incorporation of 5.1 and even 7.1 external inputs on many A/V receivers.  If your A/V receiver is equipped with 5.1 or 7.1 external audio inputs, the selection of an optical player equipped with 5.1- or 7.1 channel line-level outputs will provide full-bandwidth reproduction of the audio signal originating from your HD player.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image368.gif" alt="Connection via Multichannel Analog Inputs" align=center></td></tr><tr><td align="center">Figure 3 - Connection via Multichannel Analog Inputs</td></tr></table>

<p>A connection through either of these existing interfaces will let you experience the full potential of the high-resolution audio delivered on next-generation optical formats.</p>

<p><br />
<u>S/PDIF Connection</u></p>

<p>If your A/V receiver or processor has neither multichannel analog or digital inputs, but is equipped with 5.1-channel Dolby Digital decoding and playback, you will still be able to enjoy 5.1-channel performance from next-generation optical players.  Included within 7.1 channel multichannel Dolby Digital Plus and Dolby TrueHD streams is a core 5.1 mix prepared by the content maker that is used when the player is set for 5.1-channel mode.</p>

<p>After playback audio signals have been mixed in the player, the PCM signal can be encoded to a Dolby Digital signal and output from the player via S/PDIF (optical or coaxial) to your connected Dolby Digital A/V receiver or processor.</p>

<p>In many instances, the audio quality you will experience from this connection may be better than what you would experience during playback of standard-definition DVD Video discs, especially if the native signal on the disc is Dolby TrueHD or high-bit-rate Dolby Digital Plus.  This is a direct result of a higher-quality source signal feeding a Dolby Digital encoder running at 640 kbps-higher than the maximum bit rate on DVD-Video.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image370.gif" alt="Connection via S/PDIF" align=center></td></tr><tr><td align="center">Figure 4 - Connection via S/PDIF</td></tr></table>

<p>Because Dolby Digital encoding (of the audio mixes over the soundtrack, RLM) support is optional in HD players, you will need to look for a next-generation player equipped with an S/PDIF output and built in Dolby Digital 5.1 channel encoding technology.</p>

<p><br />
<u>Dolby TrueHD and Dolby Digital Plus in A/V Receivers</u></p>

<p>Eventually, A/V receivers will have direct access to Dolby Digital Plus or Dolby TrueHD bitstreams.  Dolby is working with the IEC and HDMI organizations to update data protocols to enable future versions of these high-bandwidth interfaces to carry these bitstreams. </p>

<p>To decode these bitstreams, the A/V decoder will need to support the updated data protocols, as well as incorporate these new decoding algorithms.  In addition, it will be necessary to select HD discs in which the content maker has permitted the core 5.1 or 7.1 audio bitstreams to bypass the player's mixing process and be sent directly to the digital outputs of the player.  We expect that certain HD discs will permit this, but they may represent a minority of titles.  In the end, the sound quality will be essentially the same as audio that was decoded in the player as PCM and transported it through a current generation HDMI connection to the A/V receiver.</p>

<p>With six or eight channels of 24 bit/96 kHz audio transported from these new HD formats, post-processing DSP requirements for an A/V receiver more than double.  Rather than devoting the considerable DSP resources to decoding the core audio signals within the A/V processor itself, it may be more fruitful to use the A/V processor's DSP resources to perform high-resolution post-processing such as bass management, room or speaker equalization, Dolby Pro Logic<sup>&reg;</sup> IIx decoding, or other types of digital signal processing.</p>

<table align="center"><tr><td><img src="/images/articles/HDTVTR2006/image372.gif" alt="Connection via Next-Generation HDMI" align=center></td></tr><tr><td align="center">Figure 5 - Connection via Next-Generation HDMI</td></tr></table>

<p>As a result of the quality and capabilities that the new digital interfaces provide, hardware manufacturers can offer more highly optimized system designs that attain the ultimate in performance while providing the greatest flexibility and efficiency for the consumer.<br />
---------------------------------------  <br />
<em>Disclaimer: This concludes the connectivity information sourced from Dolby Laboratories.</em></p>

<p><br />
<h2>Legacy Discrete Surround Audio Formats for Hi Def DVD</h2><br />
In October 2004, the DVD Forum and the Blu-ray Disc Association approved mandatory and optional audio formats for both Hi Def DVD standards.  </p>

<p>Both groups approved the legacy Dolby Digital 5.1 and DTS 5.1 discrete audio surround formats as mandatory for HD players on both formats, which also ensure the audio playability of 5.1 multi-channel DVDs when played on HD players.</p>

<p>However, both High Def DVD formats declared optional the player's ability of decoding 6.1 DTS channels.  Regarding the disc itself, at least one of the legacy formats must be included on the pre-recorded discs, at the choice of the content provider.  </p>

<p><br />
<h2>Hi-bit Surround Audio Formats - Summary</h2></p>

<p><u>Dolby Digital Plus</u><br />
Dolby Digital Plus is a flexible codec based upon core Dolby Digital technologies.  For broadcasters, it provides higher efficiency coding at lower bit rates.  For the new blue laser formats, it provides more channels, extended bit rates and higher quality. </p>

<p>The Dolby Digital Plus format was announced in April 2004 at NAB.  Dolby Digital Plus enables broadcasters to transmit 5.1 at an efficient 50% (192Kbps) data rate of regular Dolby Digital (384Kbps).  Compatibility with all existing Dolby Digital consumer decoders is ensured, as the Dolby Digital Plus signal will be upconverted to a standard 640Kbps Dolby Digital Plus output in the set top box (but the set top box that performs the upconversion would be needed).  </p>

<p>The format supports multiple languages in a single bit-stream, and was selected by the Advanced Television Systems Committee (ATSC) as the standard for future robust broadcast applications, and as an option for multichannel audio delivered by the Digital Video Broadcasting (DVB) Project for satellite and cable TV.</p>

<p>Dolby Digital Plus was also announced as capable of a higher-bit rate enhancement to Dolby's existing AC-3 (Dolby Digital) lossy audio compression format.  Dolby Digital Plus format supports new levels of quality data rates as high as 6 Mbps on 7.1 channels, with a bit-rate performance of at least 3 Mbps on HD-DVD and up to 4.7 Mbps on Blu-ray Disc.  Dolby Digital Plus has no ability to carry uncompressed audio nor can it be operated in a lossless way.</p>

<p><u>DTS-HD (and ++, Master Audio)</u><br />
DTS announced that their new lossless DTS++ (as named originally) would be capable of higher bit rates.  In October 2004, the DTS++ name was changed to DTS-HD.  In December 2005, DTS announced their demonstration of a 24 Mbps extension of the same lossless format under a new name, DTS-HD Master Audio, 100% lossless and bit-for-bit identical to the studio master, as claimed by DTS.</p>

<p>DTS-HD uses a set of extensions to the coherent acoustics audio coding system, comprised of DTS Digital Surround, DTS-ES, and DTS 96/24, which allows the format to down-mix to 5.1 and two-channel, while delivering audio quality at bit rates extending from legacy DTS Digital Surround to 7.1 DTS-HD channels, using a single stream up to 18Mbps.</p>

<p><u>Dolby TrueHD</u><br />
Dolby TrueHD can support up to 14 discrete of lossless 24-bit/96 kHz audio channels at bit rates as high as 18Mbps, although HD DVD and Blu-ray Disc standards currently limit their maximum number of audio channels to eight.  Dolby TrueHD is 100% lossless audio, delivering audio playback performances in the home that are bit-for-bit identical to studio masters, designed for next generation HD DVD and Blu-ray formats. </p>

<p></p>

<p><br />
<h2>Hi-bit Audio Application to Hi-def DVD Formats</h2></p>

<p>In September 23, 2004, Dolby Laboratories announced that the DVD Forum decided to include Dolby Digital Plus and MLP Lossless, the core audio technology behind multichannel DVD-Audio, as mandatory audio standards for HD DVD.  Later, Dolby TrueHD was also selected as mandatory audio format for HD DVD.  However, both Dolby formats were selected as optional audio formats for Blu-ray players.</p>

<p>DTS-HD was declared as optional for the players of both Hi-def DVD formats. </p>

<p>In other words, Blu-ray approved as optional the 3 hi-bit audio formats (Dolby Digital Plus, Dolby TrueHD, and DTS-HD), the only mandatory codecs for Blu-ray are the legacy 5.1 DD and DTS.   </p>

<p>According to Silicon Image, the HDMI transport is able to handle 24Mbps of audio speed, suitable for any of the proposed audio formats from either disc format, including DTS HD Master Audio.  However, the version 1.3 HDMI specification would enable the players to output those audio formats over HDMI, those protocols and specifications are expected to be finalized on the first half of 2006.  Dolby is working closely with Silicon Image to ensure transmission of Dolby Digital Plus and TrueHD signals on HDMI v. 1.3. </p>

<p>As an alternative, HD players with internal hi-bit-rate decoders are expected to also have 6.1 or 7.1 analog outputs that could support the hi-bit-rate and be connected to receivers with 6.1 or 7.1 channel analog inputs.</p>

<p><br />
<h2>Analysis</h2></p>

<p>My first human reaction:  How many more multi-channel audio formats a consumer needs?  How many more connections, A/V receivers, and audio-processors a consumer needs to continuously upgrade in this multi-channel matrix/discrete, lossy/lossless, low-bit/hi-bit, 5/6/7.1 marathon?  How many consumers actually invest beyond 5.1 low-bit lossy because they find a difference their ears and pockets justify as considerable benefit?  Where is the significant content with more channels to justify more decoding/amps/speakers at a legacy 5.1 consumer's home?  </p>

<p>The large capacity of Hi-def DVD brought with it an invitation to use part of the vast space to pursue cleaner multi-channel audio for the soundtrack of a movie, but the lossless hi-bit audio space requirements to allow a maximum/approved speed of operation of 18Mbps are reaching levels that could even exceed the space requirements of the HD video itself in the same disc (using VC1 for example), not to mention using DTS HD Master Audio maxing at 24 Mbps, the risk of exceeding the capacity of a dual layer 30GB HD DVD disc, or even a 50GB Blu-ray disc depending the combination of video and audio codecs used, and that is storing just one hi-bit lossless codec in the disc, about trying to fit two, such as the typical Dolby/DTS pair, now in hi-bit? Make the math with just one.</p>

<p>In other words even when using a dual layer approach the discs might be limited to include only one of the hi-bit multichannel audio codecs, and possibly be very close to the speed capacity of the format when a non-aggressive compression HD video codec and the hi-bit lossless audio are played simultaneously, as usually is the case.   </p>

<p>In September 2005, Dolby announced that A/V receivers capable of processing PCM over their HDMI 1.1 inputs should also be able to have sufficient bandwidth to accept the HD video and the PCM multi-channel audio decoded by the Hi-def DVD player.  Any HDMI suited receiver should be capable to input the PCM and reproduce the higher bandwidth of the soundtracks.  Initially, it was believed that those HDMI 1.1 suited A/V receivers would have to use analog cables from the multi-channel audio connectors (as with DVD-Audio), and wait until specification version 1.3 of HDMI be completed (and eventually change to a 1.3 compliant A/V receiver).  </p>

<p>According to Dolby, there should be no need to replace an HDMI 1.1 suited receiver to get the benefit of the higher-bit audio formats.  However, when using the latest HDMI version 1.3 from player to receiver, the decoding would not have to happen in the player, the connection would stream the native mandatory and optional audio formats to the HDMI 1.3 suited A/V receiver, which would perform the decoding job.  Reportedly, DTS intends to suit players as well as receivers with their decoders, Dolby was quoted as concentrating initially on players.</p>

<p>Hi-definition DVD disc players are expected to support Internet-streamed audio content (such as director's comments) while playing the movie, and they have to internally mix the various audio components (soundtrack, Internet, PCM sounds, etc) before converting the final audio mix to individual PCM channels to be output over the HDMI connection.<br />
 <br />
The newer hi-bit formats, Dolby Digital Plus lossy, Dolby TrueHD lossless, and DTS HD lossless (previously named DTS++ lossless, and now extended to Master Audio 24 Mbps), are much faster than the supported speed of typical digital coaxial connections (S/PDIF) used for the current legacy Dolby Digital and DTS multichannel audio formats, however, those legacy connections would still transport the down-converted legacy versions (derived from the hi-bit) produced by Hi-def DVD players.</p>

<p>As mentioned above, if an existing receiver does not have HDMI inputs it can still use the multichannel analog connections (6 to 8 RCA type of connections) until is time for the upgrade; remember the convenient DVD-Audio mess of wires?  </p>

<p>In selecting a Hi-def DVD player of any format, one factor of choosing one model over the other could be the implementation of the Hi-bit multichannel codecs that are optional (Dolby TrueHD, Dolby Digital Plus, DTS HD, depending on the format).  </p>

<p>Even when not having the latest A/V receiver that could decode the Hi-bit formats itself using the HDMI 1.3 connection, if a consumer is interested in a system to reproduce the optional DTS-HD for example, that consumer would be making a better investment by choosing a Hi-def player that decodes DTS-HD by itself.  The existing receiver, using the alternative connections above, would be spared from an unneeded upgrade just for that purpose.  </p>

<p>There are players that were announced with various combinations of optional codecs, one Blu-ray player was announced to support DTS HD but not Dolby TrueHD, another player supported True HD but just as a 2 channel feature.  A manufacturer is not obliged to suit the player with optional codecs, so a closer look at the specs would help on the selecting decision of the player, or even the format.   </p>

<p>Logically, the multi-cable analog connection alternative above assumes the receiver "has" those analog connections typically used for DVD-Audio, not all do.  In which case the Hi-def DVD player would do the multi-channel decoding and the D/A conversions for each of the 8 channels, 8 cables would carry them to the analog inputs of the receiver, which would convert them back to digital to perform any digital processing the receiver needs to do before the amplification stage of each channel to reach the speakers.  Quite a few conversions for that solution, not as clean as a direct digital connection but still a way to get the benefit of new codecs. </p>

<p>Since a player is also expected to make available the lower bandwidth compressed lossy versions (DD at 640kbps, and DTS at 1.5 Mbps) of those hi-bit formats over the typical S/PDIF digital connections, a consumer has another fallback plan of connectivity to older receivers, other than the analog connections, until equipment could be upgraded.  Audio mixes over the soundtrack would be missing though.</p>

<p>Although I could not verify the following in detail myself, Dolby was quoted stating that certain formats would not be able to be down-compatible 100% using the digital coax legacy connections, such as:</p>

<p>7.1 channels of 96/24 PCM at 18.4Mbps (in both Hi-def DVD formats)<br />
HD DVD's two channels of 192/24 PCM at 9.2Mbps<br />
Blu-ray's optional 6-channel uncompressed 192kHz/24-bit PCM at 27.6Mbps</p>

<p>In summary, there will be a variety of backward compatibility connectivity options to allow consumers to still be able to use the existing audio equipment at their current multi-channel audio capabilities when playing back the new audio formats of High Definition DVD discs/players, but there will be enough incentive for upgrades.  </p>

<p>Upgrading to an A/V receiver suited with HDMI 1.1 would bring the full benefit of the lossless audio formats transporting the channels digitally as PCM, and if the upgrade could be done to HDMI 1.3 connectivity it would open the possibility to do hi-bit decoding on the receiver, giving the consumer the option of doing the audio decoding in the A/V receiver or the player, which ever sounds best for the consumer, and perhaps been able to decode a hi-bit codec missing in the player but the receiver has.</p>

<p>In that scenario, the player would just stream out of HDMI the undecoded hi-bit multichannel signal for the A/V receiver to decode, expanding the flexibility of the audio part of the system.  However, check the specific conditions on the charts above, it seems that using the HDMI connection for streamed audio (not PCM) might disallow the mixing of the additional audio features of Hi-def DVD over the soundtrack, unless the player is suited with an optional, and probably unusual, "encoder".</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>April 21, 2006 07:35 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 367
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
 				AND entry_id <> 367
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/04/multi-channel_audio_for_hd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
