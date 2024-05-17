<?
	require('../global.php');

	function get_street($pg_id) {
		$result = mQuery("SELECT pg_lowest_price, alias, model_name FROM tbl_models model, tbl_companies man WHERE man.id = man_id AND pg_id = '$pg_id'");
		$row = mysql_fetch_assoc($result);
		return '<a href="'. URL_EQUIPMENT_MODEL .'?man='. rawurlencode($row[alias]) .'&model='. rawurlencode($row[model_name]) .'">$'. $row[pg_lowest_price] .'</a>';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - HD DVD Reference</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="ad-left">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">
			<h1>HD DVD Reference</h1>
			<p>
				<img src="/images/hddvd.png" alt="HD DVD" align="left" style="padding:0 5px 5px 0">Welcome to the HDTV Magazine HD DVD reference page. This page is based on our
				<a href="/articles/2006/02/hd_dvd_primer.php">HD DVD Primer</a> article published in February 2006. We will cover on this page a general overview of HD DVD technology 
				as well as digging down to the specifics of the format and comparing those with the DVD format. You will also find below a list of 
				compatible hardware/players and a list of current movie titles and their availability.
				<br clear="all" />Page Summary:<ul>
					<li><a href="#technology">General HD DVD Overview</a></li>
					<li><a href="#specifics">Format Specifics &amp; Comparisons</a></li>
					<li><a href="#hardware">Hardware Available</a></li>
					<li><a href="#titles">Titles Available</a></li>
					<li><a href="#more">Additional Links &amp; Information</a></li>
				</ul>
			</p><br />

			<a name="technology"></a>
			<h2>General HD DVD Overview</h2>
			<p>
				<img src="/images/articles/spectrum.gif" alt="Laser Spectrum" align="right" style="padding:5px">
				The basic disc structure is the same as DVD (size, layers, etc.), but the compression and laser technologies involved are completely different.
				Traditional DVD's utilized a red laser for reading to and writing from the disc. HD DVD utilizes a new blue-violet laser. This new blue-violet laser 
				has a 405nm wavelength vs. the red laser's 650nm. This shorter wavelength allows for much higher data density since the blue laser can write 
				a much narrower data track. The net effect is that HD DVD can store more than 3 times the number of bits as traditional DVD's.
			</p><p>
				HD DVD also utilizes more advanced compression techniques than did traditional DVD. It can employ MPEG-4 AVC and VC-1 (aka Windows Media 9), 
				whereas traditional DVD's were strictly MPEG-2. These compression advances allow for roughly twice the data storage as traditional DVD. For a 
				direct comparison of these two specifications, see the table below:<br clear="all" />
			</p><p>
				In addition to improved video, HD DVD also features expanded audio support. Traditional DVD provided support for AC-3 (Dolby<sup>&reg;</sup> Digital) and 
				MPEG codecs. HD DVD has added Dolby<sup>&reg;</sup> Digital Plus (lossy) and DTS<sup>&reg;</sup> (lossy) as mandatory codecs. Support for 2-channel 
				Linear PCM and 2-channel MLP (True HD) are also mandated. The HD DVD standard also allows for DTS<sup>&reg;</sup> HD (lossless) as an optional codec. 
				With respect to the two currently available Toshiba units, the audio capabilities are equally as impressive as their video capabilities. Their press release says it best:
				<blockquote>
					The mandatory audio formats for HD DVD include both lossy and lossless formats from Dolby Labs and DTS<sup>&reg;</sup> - including the newly 
					developed Dolby<sup>&reg;</sup> Digital Plus and DTS-HD.<br>
					<br>
					The lossless mandatory formats include Linear PCM and Dolby TrueHD (only 2 Channel support is mandatory). The TrueHD format is bit-for-bit identical 
					to the high resolution studio masters and can support up to eight discrete full range channels of 24-bit/96k Hz audio. Another lossless format (specified 
					as an optional format) is DTS-HD. This employs high sampling rates of up to 192kHz.<br>
					<br>
					Both models feature built-in multi-channel decoders for Dolby Digital, Dolby Digital Plus, Dolby TrueHD (2 channel), DTS and DTS-HD. The HD-XA1 employs 
					the use of four high performance DSP engines to decode the multi-channel streams of the wide array of audio formats. These high performance processors 
					will perform the required conversion process, as well as the extensive on-board Multi-Channel Signal Management including: User Selectable Crossovers, 
					Delay Management and Channel Level Management.
				</blockquote>
			</p><p>
				For content protection, HD DVD utilizes the Advanced Access Content System (AACS), which is a standard for content distribution and digital rights management. 
				For media that is AACS-enabled, these players will be required to recognize an Image Constraint Token (ICT), inserted into the movie data, and scale the analog output 
				(over component) down to 540p.  This is still better than standard 480p DVD, but far from HD resolutions. As well as preventing illegal copying, AACS provides 
				"Managed Copy", which essentially allows content transfer from the HD DVD to other device (like a home media server). The decision to ICT-enable content is up to each 
				studio, and each studio is likely to go their own way.
			</p><br />

			<a name="specifics"></a>
			<h2>Format Specifics &amp; Comparisons</h2>
			<table class="type1b" cellpadding="0" cellspacing="0">
				<tr><td>&nbsp;</td><td style="text-align:center" class="type1b_header">DVD</th><th colspan="3" class="type1b_header">HD DVD</th></tr>
				<tr><th style="text-align:center" class="type1b_header">Disc type</th><td style="text-align:center" class="grid">DVD-ROM<br>(Read-Only)</td><td style="text-align:center" class="grid">HD DVD-ROM<br>(Read-Only)</td><td style="text-align:center" class="grid">HD DVD-R<br>(Recordable) </td><td style="text-align:center" class="grid">HD DVD-Rewritable<br>(Recordable)</td></tr>
				<tr><th class="type1b_header">Disc diameter</th><td style="text-align:center" class="grid">120mm</td><td style="text-align:center" class="grid">120mm</td><td style="text-align:center" class="grid">120mm</td><td style="text-align:center" class="grid">120mm</td></tr>
				<tr><th class="type1b_header">Disc structure</th><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td><td style="text-align:center" class="grid">0.6mm<br>x 2 substrates</td></tr>
				<tr><th class="type1b_header">Capacity<br>(Single-sided,<br>single-layer)<br>(Single-sided,<br>dual-layer)</th><td style="text-align:center" class="grid">4.7GB<br>8.5GB</td><td style="text-align:center" class="grid">15GB<br>30GB</td><td style="text-align:center" class="grid">15GB</td><td style="text-align:center" class="grid">20GB<br>32GB<span class="text_hd5">(Under development)</td></tr>
				<tr><th class="type1b_header">Playback time*<br>Recording time*</th><td style="text-align:center" class="grid"><span class="text_hd5">4.7GB, SD resolution:<br>132minutes<br><span class="text_hd5">8.5GB, SD resolution:<br>238minutes</td><td style="text-align:center" class="grid"><span class="text_hd5">15GB, HD resolution:<br>over 4 hours<br><span class="text_hd5">30GB, HD resolution:<br>over 8 hours</td><td style="text-align:center" class="grid"><span class="text_hd5">15GB, HD resolution:<br>over 4 hours</td><td style="text-align:center" class="grid"><span class="text_hd5">20GB, HD resolution:<br>over 5.5 hours<br><span class="text_hd5">32GB, HD resolution:<br>over 8.5 hours </td></tr>
				<tr><th class="type1b_header">Laser Wavelength</th><td style="text-align:center" class="grid">650nm<br>(red laser)</td><td style="text-align:center" class="grid">405nm<br>(blue laser)</td><td style="text-align:center" class="grid">405nm<br>(blue laser)</td><td style="text-align:center" class="grid">405nm<br>(blue laser)</td></tr>
				<tr><th class="type1b_header">Compression<br>technology</th><td style="text-align:center" class="grid">MPEG-2</td><td style="text-align:center" class="grid">MPEG-4 AVC/<br>VC-1/MPEG-2</td><td style="text-align:center" class="grid">MPEG-4 AVC/<br>VC-1/MPEG-2</td><td style="text-align:center" class="grid">MPEG-4 AVC/<br>VC-1/MPEG-2 </td></tr>
				<tr><th class="type1b_header">User bit rate</th><td style="text-align:center" class="grid">11.08Mbps</td><td style="text-align:center" class="grid">36.55Mbps</td><td style="text-align:center" class="grid">36.55Mbps</td><td style="text-align:center" class="grid">36.55Mbps</td></tr>
				<tr><th class="type1b_header">Track pitch</th><td style="text-align:center" class="grid">0.74&micro;m</td><td style="text-align:center" class="grid">0.40&micro;m</td><td style="text-align:center" class="grid">0.40&micro;m</td><td style="text-align:center" class="grid">0.34&micro;m</td></tr>
			</table><br />
			
			<a name="hardware"></a>
			<h2>Hardware Available</h2>
			<p>
				Originally, the only manufacturer that had HD DVD players on the market was Toshiba, but recently RCA has nudged into the market. The 
				<a href="<?=URL_EQUIPMENT_MODEL?>?man=Toshiba&model=HDXA1">HD-XA1</a>
				($799.99) and the <a href="<?=URL_EQUIPMENT_MODEL?>?man=Toshiba&model=HDA1">HD-A1</a> ($499.99)
				are both backward-compatibile, allowing playback of older CD and DVD formats. Both players also support copy-protected playback via HDCP at 720p and 1080i over 
				HDMI, and will scale a traditional 480p DVD source to either 720p or 1080i to match your television's capabilities.
			</p><p>
				Toshiba also has two second-generation HD DVD players coming out by the end of the year. The 
				<a href="<?=URL_EQUIPMENT_MODEL?>?man=Toshiba&model=HDA2">Toshiba HD-A2</a>
				($499.99) and the HD-XA2 ($999.99) will both support more advanced audio, while the HD-XA2 will be the first player to support 1080p output and will be 
				HDMI 1.3 compliant. Both models also load the media in about half the time of their first-generation counterparts and also sport an improved remote control.
			</p><p>
				While not a "player" in and of itself, Microsoft will be releasing the <a href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/gp/product/B000JHO4L0/102-4793626-9133766?ie=UTF8&tag=hdtvmagazine-20&linkCode=xm2&camp=1789&creativeASIN=B000JHO4L0">HD DVD add-on drive for the Xbox 360</a>
				in November 2006. This drive, when combined with the Xbox 360 Gaming Console will be fully compliant with the HD DVD specification and is rumored to support
				1080p output via VGA connector.
			</p>
			
			<!--table width="100%"><tr>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E1PTGK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2"><img src="/images/articles/hd-a1.jpg" alt="Toshiba HD-A1"></a></td>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E21TY0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2"><img src="/images/articles/hd-xa1.jpg" alt="Toshiba HD-XA1"></a></td>
			</tr><tr>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E1PTGK/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Toshiba HD-A1</a></td>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/exec/obidos/ASIN/B000E21TY0/hdtvmagazine-20/002-3176110-8239259?%5Fencoding=UTF8&camp=1789&link%5Fcode=xm2">Toshiba HD-XA1</a></td>
			</tr><tr>
				<td colspan="2">&nbsp;</td>
			</tr><tr>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/gp/product/B000IJV4BC/102-4793626-9133766?ie=UTF8&tag=hdtvmagazine-20&linkCode=xm2&camp=1789&creativeASIN=B000IJV4BC"><img src="/images/bulletins/toshiba-HD-A2.gif" alt="Toshiba HD-A2"></a></td>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/gp/product/B000JHO4L0/102-4793626-9133766?ie=UTF8&tag=hdtvmagazine-20&linkCode=xm2&camp=1789&creativeASIN=B000JHO4L0"><img src="/images/microsoft-xbox-360-hd-dvd.jpg" alt="Microsoft Xbox 360 HD DVD Add-on"></a></td>
			</tr><tr>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/gp/product/B000IJV4BC/102-4793626-9133766?ie=UTF8&tag=hdtvmagazine-20&linkCode=xm2&camp=1789&creativeASIN=B000IJV4BC">Toshiba HD-A2</a></td>
				<td style="text-align:center"><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?http://www.amazon.com/gp/product/B000JHO4L0/102-4793626-9133766?ie=UTF8&tag=hdtvmagazine-20&linkCode=xm2&camp=1789&creativeASIN=B000JHO4L0">Microsoft Xbox 360 HD DVD Add-on</a></td>
			</tr></table><br /-->
			
			<table class="type1b">
				<tr>
					<td class="type1b_header">&nbsp;</td>
					<td class="type1b_header" align="center">Output Resolution</td>
					<td class="type1b_header" align="center">HDMI Version</td>
					<td class="type1b_header" align="center">Avialability</td>
					<td class="type1b_header" align="center">Retail</td>
					<td class="type1b_header" align="center">Street</td>
				</tr><tr>
					<td class="type1b_header" align="center">Toshiba HD-A1</td>
					<td class="grid" align="center">720p, 1080i</td>
					<td class="grid" align="center">1.1</td>
					<td class="grid" align="center">Now</td>
					<td class="grid" align="center">$499.99</td>
					<td class="grid" align="center"><?=get_street(17247256);?></td>
				</tr><tr>
					<td class="type1b_header" align="center">Toshiba HD-XA1</td>
					<td class="grid" align="center">720p, 1080i</td>
					<td class="grid" align="center">1.1</td>
					<td class="grid" align="center">Now</td>
					<td class="grid" align="center">$799.99</td>
					<td class="grid" align="center"><?=get_street(17247255);?></td>
				</tr><tr>
					<td class="type1b_header" align="center">Toshiba HD-A2</td>
					<td class="grid" align="center">720p, 1080i</td>
					<td class="grid" align="center">1.2a</td>
					<td class="grid" align="center">October 2006</td>
					<td class="grid" align="center">$499.99</td>
					<td class="grid" align="center"><?=get_street(27344348);?></td>
				</tr><tr>
					<td class="type1b_header" align="center">Toshiba HD-XA2</td>
					<td class="grid" align="center">720p, 1080i, 1080p</td>
					<td class="grid" align="center">1.3</td>
					<td class="grid" align="center">December 2006</td>
					<td class="grid" align="center">$999.99</td>
					<td class="grid" align="center">N/A</td>
				</tr><tr>
					<td class="type1b_header" align="center">RCA HDV5000</td>
					<td class="grid" align="center">720p, 1080i</td>
					<td class="grid" align="center">1.1</td>
					<td class="grid" align="center">Now</td>
					<td class="grid" align="center">$499.99</td>
					<td class="grid" align="center"><?=get_street(19958033);?></td>
				</tr><tr>
					<td class="type1b_header" align="center">Microsoft Xbox 360<br />HD DVD Add-on</td>
					<td class="grid" align="center">720p, 1080i, 1080p</td>
					<td class="grid" align="center">N/A</td>
					<td class="grid" align="center">November 2006</td>
					<td class="grid" align="center">$199.99</td>
					<td class="grid" align="center"><?=get_street(28490405);?></td>
				</tr>
			</table><br />
			
			<a name="titles"></a>
			<h2>Titles Available</h2>
				<?
					$sql = "
					SELECT detailpageurl, title, smallimageurl, lowestnewprice, lowestusedprice, averagerating, runningtime, aspectratio
					FROM az_main m, az_attributes a
					WHERE m.asin = a.asin
						AND a.binding = 'HD DVD'
						AND smallimageurl <> ''
					ORDER BY title";
					$result = mQuery($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$rating = ($row[averagerating] == '') ? '' : '<img src="/images/stars_'. $row[averagerating] .'.gif" alt="'. $row[averagerating] .'" align="absmiddle">';
						$used = ($row[lowestusedprice] > 0) ? '	Used: $'. number_format($row[lowestusedprice]/100, 2) .'<br />' : '';
						echo '<div class="block_small">'.
						'	<a target="_blank" href="'. $row[detailpageurl] .'"><img src="'. $row[smallimageurl] .'" alt="'. $row[title] .'" align="left" style="padding-right:2px" /></a>'.
						'	<a target="_blank" href="'. $row[detailpageurl] .'">'. str_replace(array(' [HD DVD]', ' (Combo HD DVD and Standard DVD)'), '', $row[title]) .'</a> - '. $rating .'<br />'.
						'	New: $'. number_format($row[lowestnewprice]/100, 2) .'<br />'.
						$used .
						$row[runningtime] .'m, '. $row[aspectratio] .'<br />'.
						'</div>';
					}
				?>
			<br clear="all" /><br />
			
			<!--table class="type1b"><tr>
				<td class="type1b_header">Title</td><td class="type1b_header">Date</td><td class="type1b_header">Studio</td>
			</tr></table-->
			
			<a name="more"></a>
			<h2>Additional Links &amp; Information</h2>
			<ul>
				<li><a href="/articles/2006/08/hd_dvd_vs_blu-ray_and_the_winner_is_no_one.php">HD DVD vs. Blu-ray: And the Winner is ... No One</a></li>
				<li><a href="/articles/2006/02/hd_dvd_primer.php">HD DVD Primer</a></li>
				<li><a href="/cgi-bin/ntlinktrack.cgi?http://www.hddvdprg.com/">The HD DVD Promotion Group</a></li>
				<li><a href="/cgi-bin/ntlinktrack.cgi?http://www.tacp.toshiba.com/news/newsarticle.asp?newsid=113">Toshiba Press Release</a></li>
				<li><a href="/cgi-bin/ntlinktrack.cgi?http://www.toshiba.co.jp/hddvd/eng/index.htm">Toshiba Corporation</a></li>
			</ul>

		</td>
	</tr></table></div>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
