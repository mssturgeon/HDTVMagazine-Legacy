<?
	require('../global.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - </title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<script language="javascript" type="text/javascript">
	function isValid() {
		alert('here');
		email = frm.email_address;
		alert(email.value);
		if (isValidEmail(email)) {
			alert(email.value +': Valid');
		} else {
			alert(email.value +': NOT Valid');
		}
	}
</script>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

				<div align="center"><h1></h1></div>
				<form target="<?=PHP_SELF?>" method="post" name="frm">
				Email Address: <input type="text" name="email_address" value="">
				<input type="button" onclick="isValid()" value="Check">
				</form>

		<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
