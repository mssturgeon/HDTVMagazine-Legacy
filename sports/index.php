<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - HDTV Sports</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<h1>HDTV Sports</h1>
	<p>
		Welcome to the new Sports page. This page slowly being built-up and added to, so if you have any thoughts or ideas for improving it,
		please send them in via the <a href="<?=URL_FEEDBACK?>">feedback form</a>.
	</p>
	<?
		$sql = "
		SELECT * FROM "
	?>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
