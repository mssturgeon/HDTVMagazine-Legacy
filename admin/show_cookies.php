<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
				<h1>Admin - Show Cookies</h1>
				
				<pre>
				<?
					print_r($_COOKIE);
				?>
				</pre>
				
	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
