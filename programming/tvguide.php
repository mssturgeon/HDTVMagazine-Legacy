<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Programming Guide</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="refresh" content="600" />
</head>
<body>
	<?
		include(BASE_DIR .'/ads/leaderboard.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="ad-left">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">

			<iframe src="http://listings2go.tvguide.com/PartnerGrid/grids?partnerid=124&profileid=467" frameborder="0" width="100%" height="600" style="margin:0" />

		</td>
	</tr></table></div>
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
