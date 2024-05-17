<?require('../global.php');?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Content &amp; Programming</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="A list of various sources for HDTV Programming and Content along with TV Guide and schedules">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>HDTV Programming &amp; Content</h1>
	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<?include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>
	
	<div>
		<div align="center"><div class="alertbox" style="display:table">
			<table class="bare"><tr>
				<td style="vertical-align:middle; padding-right:5px;"><a href="<?=URL_GUIDE?>"><img src="/images/screenshots/program-guide.gif" alt="HDTV Program Guide" /></a></td>
				<td style="text-align:left">
					<b>Looking for the HDTV Programming Guide?</b>
					The <a href="<?=URL_GUIDE?>">HDTV Programming Guide</a> has not moved, it is still located here:
					<a href="<?=URL_GUIDE?>">http://www.hdtvmagazine.com/programming/guide.php</a>. We have just added this page to expand your
					view of what programming is available through other media.
				</td>
			</tr></table>
		</div></div>
	
		<p>
			Ever since HDTV began to make its way into the home, "programming" has been continuously cited as a reason to hold off on an HDTV purchase.
			As you can see below, that is clearly no longer the case. HDTV can be had through almost every medium imaginable: <a href="#internet">Internet</a>, 
			<a href="#packaged">Packaged media</a> (HD DVD, Blu-ray, D-VHS, WMVHD), <a href="#gaming">PC/Console gaming</a>, <a href="#fiber">Fiber-Optic</a>, and of course good ol' <a href="#ota">Over-the-air broadcast</a>,
			<a href="#satellite">Satellite</a> and <a href="#cable">Cable</a>. As you look through the myriad
			HD programming options below, please <a href="<?=URL_HELP_FEEDBACK?>">let us know</a> if we've missed anything.
		</p>
	
		<br /><a name="ota"></a>
		<h2>Over-the-air Broadcast</h2>
		<p></p>
		<div class="networkLogo"><a href="/programming/network/abc.php"><img src="/images/logos/abc_75x40.gif" alt="ABC" /></a></div>
		<div class="networkLogo"><a href="/programming/network/cbs.php"><img src="/images/logos/cbs_75x40.gif" alt="CBS" /></a></div>
		<div class="networkLogo"><a href="/programming/network/cw.php"><img src="/images/logos/cw_75x40.gif" alt="CW" /></a></div>
		<div class="networkLogo"><a href="/programming/network/fox.php"><img src="/images/logos/fox_75x40.gif" alt="FOX" /></a></div>
		<div class="networkLogo"><a href="/programming/network/nbc.php"><img src="/images/logos/nbc_75x40.gif" alt="NBC" /></a></div>
		<div class="networkLogo"><a href="/programming/network/pbs.php"><img src="/images/logos/pbs_75x40.gif" alt="PBS" /></a></div>
		<br clear="left" /><br />
		
		<br /><a name="satellite"></a>
		<h2>Satellite</h2>
		<p>
			Satellite providers listed alphabetically:
		</p>
		<div class="networkLogo"><a href="/programming/bell-expressvu.php"><img src="/images/logos/satellite-bell-expressvu_75x40.gif" alt="Bell ExpressVu" /></a></div>
		<div class="networkLogo"><a href="/programming/satellite/directv.php"><img src="/images/logos/directv_75x40.gif" alt="DirecTV" /></a></div>
		<div class="networkLogo"><a href="/programming/dish-network.php"><img src="/images/logos/dish-network_75x40.gif" alt="Dish Network" /></a></div>
		<div class="networkLogo"><a href="/programming/sky.php"><img src="/images/logos/satellite-sky_75x40.gif" alt="Sky" /></a></div>
		<div class="networkLogo"><a href="/programming/star-choice.php"><img src="/images/logos/satellite-star-choice_75x40.gif" alt="Star Choice" /></a></div>
		<br clear="left" /><br />
		
		<br /><a name="cable"></a>
		<h2>Cable</h2>
		<p>
			The list below is not all-inclusive, but we have have listed the top cable companies in the U.S. and Canada.
		</p>
		<div class="networkLogo"><a href="/programming/comcast.php"><img src="/images/logos/cable-comcast_75x40.gif" alt="Comcast" /></a></div>
		<div class="networkLogo"><a href="/programming/time-warner.php"><img src="/images/logos/cable-time-warner_75x40.gif" alt="Time Warner" /></a></div>
		<div class="networkLogo"><a href="/programming/charter.php"><img src="/images/logos/cable-charter_75x40.gif" alt="Charter" /></a></div>
		<div class="networkLogo"><a href="/programming/cox.php"><img src="/images/logos/cable-cox_75x40.gif" alt="Cox" /></a></div>
		<div class="networkLogo"><a href="/programming/adelphia.php"><img src="/images/logos/cable-adelphia_75x40.gif" alt="Adelphia" /></a></div>
		<div class="networkLogo"><a href="/programming/cablevision.php"><img src="/images/logos/cable-cablevision_75x40.gif" alt="Cablevision" /></a></div>
		<div class="networkLogo"><a href="/programming/bright-house.php"><img src="/images/logos/cable-bright-house_75x40.gif" alt="Bright House Networks" /></a></div>
		<div class="networkLogo"><a href="/programming/mediacom.php"><img src="/images/logos/cable-mediacom_75x40.gif" alt="Mediacom" /></a></div>
		<div class="networkLogo"><a href="/programming/insight.php"><img src="/images/logos/cable-insight_75x40.gif" alt="Insight" /></a></div>
		<div class="networkLogo"><a href="/programming/suddenlink.php"><img src="/images/logos/cable-suddenlink_75x40.gif" alt="Suddenlink" /></a></div>
		<div class="networkLogo"><a href="/programming/cableone.php"><img src="/images/logos/cable-cableone_75x40.gif" alt="CableOne" /></a></div>
		<div class="networkLogo"><a href="/programming/rcn.php"><img src="/images/logos/cable-rcn_75x40.gif" alt="RCN" /></a></div>
		<div class="networkLogo"><a href="/programming/wideopenwest.php"><img src="/images/logos/cable-wow_75x40.gif" alt="WOW" /></a></div>
		<div class="networkLogo"><a href="/programming/rogers.php"><img src="/images/logos/cable-rogers_75x40.gif" alt="Rogers" /></a></div>
		<div class="networkLogo"><a href="/programming/shaw.php"><img src="/images/logos/cable-shaw_75x40.gif" alt="Shaw" /></a></div>
		<div class="networkLogo"><a href="/programming/videotron.php"><img src="/images/logos/cable-videotron_75x40.gif" alt="Videotron" /></a></div>
		<div class="networkLogo"><a href="/programming/cogeco.php"><img src="/images/logos/cable-cogeco_75x40.gif" alt="Cogeco" /></a></div>
		<br clear="left" /><br />
		
		<br /><a name="fiber"></a>
		<h2>Fiber-Optic</h2>
		<p></p>
		<div class="networkLogo"><a href="/programming/verizon-fios.php"><img src="/images/logos/fiber-verizon_75x40.gif" alt="Verizon FIOS" /></a></div>
		<br clear="left" /><br />
		
		<br /><a name="internet"></a>
		<h2>Internet</h2>
		<p></p>
		<div class="networkLogo"><a href="/programming/apple.php"><img src="/images/logos/internet-apple_75x40.gif" alt="Apple Movie Trailers" /></a></div>
		<!--div class="networkLogo"><a href="/programming/instant-media.php"><img src="/images/logos/internet-instant-media_75x40.gif" alt="Instant Media" /></a></div-->
		<div class="networkLogo"><a href="/programming/podcasts.php"><img src="/images/logos/internet-podcasting_75x40.gif" alt="iTunes HD Podcasts" /></a></div>
		<div class="networkLogo"><a href="/programming/wmvhd.php"><img src="/images/logos/internet-wmvhd_75x40.gif" alt="Windows Media Video HD" /></a></div>
		<br clear="left" /><br />
		
		<br /><a name="packaged"></a>
		<h2>Packaged Media</h2>
		<p></p>
		<div class="networkLogo"><a href="/programming/blu-ray.php"><img src="/images/logos/packaged-blu-ray_75x40.gif" alt="Blu-ray" /></a></div>
		<div class="networkLogo"><a href="/programming/d-vhs.php"><img src="/images/logos/packaged-d-vhs_75x40.gif" alt="D-VHS" /></a></div>
		<div class="networkLogo"><a href="/programming/hd-dvd.php"><img src="/images/logos/packaged-hd-dvd_75x40.gif" alt="HD DVD" /></a></div>
		<div class="networkLogo"><a href="/programming/wmvhd.php"><img src="/images/logos/internet-wmvhd_75x40.gif" alt="WMVHD" /></a></div>
		<br clear="left" /><br />
		
		<br /><a name="gaming"></a>
		<h2>PC/Console Gaming</h2>
		<p></p>
		<div class="networkLogo"><a href="/programming/ps2.php"><img src="/images/logos/gaming-ps2_75x40.gif" alt="PlayStation2" /></a></div>
		<div class="networkLogo"><a href="/programming/ps3.php"><img src="/images/logos/gaming-ps3_75x40.gif" alt="PlayStation3" /></a></div>
		<div class="networkLogo"><a href="/programming/xbox.php"><img src="/images/logos/gaming-xbox_75x40.gif" alt="Xbox" /></a></div>
		<div class="networkLogo"><a href="/programming/xbox-360.php"><img src="/images/logos/gaming-xbox-360_75x40.gif" alt="Xbox 360" /></a></div>
		<div class="networkLogo"><a href="/programming/pc.php"><img src="/images/logos/gaming-pc_75x40.gif" alt="PC Gaming" /></a></div>
		<br clear="left" /><br />
	</div>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
