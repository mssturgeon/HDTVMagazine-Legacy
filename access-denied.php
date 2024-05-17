<?
	require('global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Access Denied</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

				<h1>Access Denied</h1>
				<table class="box" align="center"><tr><td>
					Sorry, you have attempted to access a page for which you do not have permission.<br>
					If you believe you have received this message in error, please use our <a href="<?=URL_HELP_FEEDBACK?>">Feedback Form</a>.<br>
					<br>
					If you are an existing user attempting to access the HDTV Program Guide, please note that this is now a pay feature of HDTV Magazine. For more information, please visit the <a href="<?=URL_GUIDE?>">HDTV Program Guide</a> page.
				</td></tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>

</body>
</html>
