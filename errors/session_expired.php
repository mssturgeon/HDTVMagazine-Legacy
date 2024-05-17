<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Session Expired</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
	<h1>Session Expired</h1>
	<p>
		Your browser session has expired.  Please begin a new session from the <a href="<?=URL_STORE?>">Main Order Page</a>.
	</p><p>
		If you have any questions about whether your order was completed properly, please use our <a href="<?=URL_HELP_FEEDBACK?>?category=online_order">Feedback form</a> or call our toll-free number (listed below).
	</p><p>
		<br>
		Thank you,<br>
		<br>
		Dale &amp; Shane<br>
		HDTV Magazine<br>
		1-800-LOV-HDTV (1-800-568-4388)<br>
	</p>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
