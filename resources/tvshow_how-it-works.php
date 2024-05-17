<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - HDTV: How it Works, What to Buy</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<h1>HDTV: How it Works, What to Buy</h1>
	<p>
		The following 25 minute TV program about HDTV features our very own <a href="<?=URL_ABOUT_CONTACT?>?name=lamaestra">Rodolfo La Maestra</a> and was recorded in May, 2004 at the studios of Montgomery Television in Rockville, MD.
		This show already aired on MD Cable Television during 2004. It will rerun with other technology programs throughout this year and will be continued with further episodes.
		The program was produced by <a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.cpcug.org/">The Capital PC User Group, Inc.</a> (CPCUG).
		The CPCUG is the third largest PC-based user group organization in the United States, and is located in the Washington DC area.
	</p><p>
		Although the content is over one year old,  the majority of the information mentioned in the program is still very applicable to today's HDTV environment/marketplace.
		The content was targeted to a technically-oriented audience, but it would also be beneficial to general consumers, and future HDTV owners
		 ... so that everyone can enjoy HDTV without spending a fortune.
	</p><p>
		So enjoy the program, and if you have any questions we are <a href="<?=URL_HELP_FEEDBACK?>">glad to help</a>. You will find below both a "streaming" version and downloadable versions, for those
		of you who may not be able to get the streaming version to work properly..
	</p>

	<table class="bare" cellpadding="0" cellspacing="0" align="center"><tr>
		<td style="padding-right:15px;vertical-align:top;">
			<table class="type1b" cellpadding="2" cellspacing="0" align="center">
				<tr>
					<td class="type1b_header">Format/Type *</td>
					<td class="type1b_header">Duration</td>
					<td class="type1b_header">File size</td>
					<td class="type1b_header">Bit rate</td>
				</tr><tr>
					<td class="grid">
						<img src="/images/wmv.gif" alt="WMV">&nbsp;&nbsp;
						<a href="/downloads/show.wmv">WMV (Broadband)</a>
					</td>
					<td class="grid">25:08</td>
					<td class="grid">306 MB</td>
					<td class="grid">819 Kbps</td>
				</tr><tr>
					<td class="grid">
						<img src="/images/real.gif" alt="Real">&nbsp;&nbsp;
						<a href="/downloads/show_broadband.rmvb">RealMedia (Broadband)</a>
					</td>
					<td class="grid">25:08</td>
					<td class="grid">313 MB</td>
					<td class="grid">600 Kbps</td>
				</tr><tr>
					<td class="grid">
						<img src="/images/real.gif" alt="Real">&nbsp;&nbsp;
						<a href="/downloads/show_modem.rmvb">RealMedia (Modem)</a>
					</td>
					<td class="grid">25:08</td>
					<td class="grid">23.3 MB</td>
					<td class="grid">40 Kbps</td>
				</tr><tr>
					<td colspan="6" style="border:0px;">
						* If downloading, you may want to "Right-click, Save as..." on the URL's above. Otherwise the video may begin streaming locally to your PC depending on your browser settings.
					</td>
				</tr>
			</table>
			<br><br><br>
			<b>Special Note:</b> Due to the current popularity of this page, there may be significant delay with the streaming video to the right. If you can afford the wait, I highly suggest one of the downloadable versions above.
			We are also working on adding additional formats to those listed above, and welcome your <a href="<?=URL_HELP_FEEDBACK?>">comments</a>.
		</td><td>
			<!--object id="MediaPlayer" width="320" height="240" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">
				<param name="FileName" value="/downloads/show.wmv">
				<param name="animationatStart" value="false">
				<param name="transparentatStart" value="false">
				<param name="autoStart" value="false">
				<param name="showControls" value="true">
				<embed type="application/x-mplayer2" pluginspage = "http://www.microsoft.com/Windows/MediaPlayer/" SRC="/downloads/show.wmv" name="MediaPlayer" width="320" height="240"></embed>
			</object-->
		</td>
	</tr></table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
