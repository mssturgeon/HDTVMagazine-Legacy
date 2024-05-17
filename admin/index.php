<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
<title>HDTV Magazine Administration</title>
</head>
<frameset cols="225, *" frameborder="0" border="1">
	<frame name="nav" src="index-nav.php">
	<frame name="main" src="status-report.php">
</frameset>
</html>