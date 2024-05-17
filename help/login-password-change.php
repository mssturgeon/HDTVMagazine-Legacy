<?
	require('../global.php');
	if (!$user->data['is_registered']) prompt_login($_SERVER['PHP_SELF']);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		// Check username and old password
//		$old_password = addslashes($_POST['old_password']);
//		$sql = "SELECT username FROM ". USERS_TABLE ." WHERE user_id = ". $user->data['user_id'] ." AND user_password = '". md5($old_password) ."'";
//		$result = mQuery($sql);
//		$row = mysql_fetch_assoc($result);
//		if (mysql_num_rows($result) == 0) {
//			js_back('Invalid old password ('. $old_password .'), please re-enter.');
//			exit;
//		}

		# Update USERS_TABLE
		mQuery("UPDATE ". USERS_TABLE ." SET user_password = '". md5($_POST['new_password']) ."' WHERE user_id = ". $user->data['user_id']);

		# Update user table
		mQuery("UPDATE user SET password = '{$_POST['new_password']}' WHERE bb_id = '". $user->data['user_id'] ."'");

		js_alert('Password reset successfully.');
		js_replace('/profile.php');
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Change Password</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function validate(frm) {
			with (frm) {
				if (old_password.value == '') {
					alert('You must provide your Old Password.');
					old_password.focus();
					return false;
				}

				if (new_password.value != new_password_confirm.value) {
					alert('New Passwords do not match!');
					new_password.focus();
					return false;
				}
			}
			return true;
		}
	</script>
</head>
<body id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Change Password</h1>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<form method="post" name="frmResetPassword" action="<?=PHP_SELF?>" onSubmit="return validate(this)">
		<input type="hidden" name="action" value="submit">
		<fieldset>
			<legend>Change Password</legend>
			<label for="old_password">Old Password:</label>
			<input type="password" id="old_password" name="old_password" maxlength="50" value="" class="inputText" maxlength="50" /><br />
			<br />

			<label for="new_password">New Password:</label>
			<input type="password" id="new_password" name="new_password" maxlength="50" value="" class="inputText" maxlength="50" /><br />
			<br />

			<label for="new_password_confirm">Confirm New Password:</label>
			<input type="password" id="new_password_confirm" name="new_password_confirm" maxlength="50" value="" class="inputText" maxlength="50" /><br />
			<br />

			<input type="submit" class="inputButton" value="Change Password">
		</fieldset>
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>