<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Apple Quicktime HD Gallery</title>
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
      	<h1>Apple HD</h1>
			<h2>Quicktime HD Gallery</h2>
      	<p>
      		Apple maintains an HD Gallery on their website. This gallery features Quicktime videos of movie trailers and other documentary-style shorts that are available
				in a variety of formats. These videos are encoded using the H.264 codec (a subset of MPEG-4), which is able to achieve very high data compression without
				loss in picture quality. Apple has a page of 
				<a href="/cgi-bin/ntlinktrack.cgi?http://www.apple.com/quicktime/guide/hd/recommendations.html">recommended systems</a> for playing back this video of course.<br />				
				<br />
				HD Gallery: <a href="/cgi-bin/ntlinktrack.cgi?http://www.apple.com/quicktime/guide/hd/">http://www.apple.com/quicktime/guide/hd/</a>
			</p>
			
			<br />
			<h2>More to come ...</h2>
		</td>
	</tr></table></div>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
