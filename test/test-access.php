<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Test Access</title>
	<meta name="description" content="">
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv">
	<meta name="rating" content="general">
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		
		echo "User Access: $_SESSION[access]<br />";
		if (access(ACCESS_ADMIN)) echo "ACCESS_ADMIN<br>";
		if (access(ACCESS_ADMIN_NEWS)) echo "ACCESS_ADMIN_NEWS<br>";
		if (access(ACCESS_ADMIN_ANY)) echo "ACCESS_ADMIN_ANY<br>";

		# Check Forum Access
		if (true && ($_SESSION[access] & ACCESS_PREMIUM)) {
			echo 'not premium access';
		}
		
	include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
