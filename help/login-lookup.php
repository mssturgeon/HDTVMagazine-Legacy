<?
	require('../global.php');

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'submit') {
		$sql = "
		SELECT b.user_id, username, password, b.user_email
		FROM user u, ". USERS_TABLE ." b
		WHERE u.bb_id = b.user_id
			AND user_email = '{$_POST['user_email']}'";
#		echo "$sql<br />";
		$result = mQuery($sql);
		if (mysql_num_rows($result) == 0) {
			js_back('There was no username found with that email address ('. $_POST['user_email'] .')');
			exit;
		}
		$row_user = mysql_fetch_assoc($result);

		# Make sure password retreived is properly set
		$sql = "UPDATE ". USERS_TABLE ." SET user_password = '". md5($row_user['password']) ."' WHERE user_id = {$row_user['user_id']}";
#		echo "$sql<br />";
		mQuery($sql);

		// Compose and send email
		$message = addslashes(file_get_contents("http://www.hdtvmagazine.com/admin/emails/login-info-text.php?u={$row_user['user_id']}&no_session"));
		$sql = "
		INSERT INTO email_queue
			(send_to, subject, message, content_type, unsub_code)
			VALUES ('{$row_user['user_email']}', 'Your requested login credentials', '$message', 'text/plain', '')";
		mQuery($sql);

		js_alert('Your login information has just been emailed to "'. $row_user['user_email'] .'".');
		js_redirect('/login.php');
		exit;
	}
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Username/Password Lookup</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Username/Password Lookup</h1>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<?include(BASE_DIR .'/ads/mrectangle.php');?>
	</div>

	<p>
		Please provide the email address with which you registered below and your username and password will be emailed to you immediately.
	</p>

	<form method="post" name="frmLookupUsername" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return validate(this)">
		<input type="hidden" name="action" value="submit">
		<b>Email Address:</b>
		<input type="text" class="inputText" name="user_email" value="<?=$_GET['email_address']?>" size="30" maxlength="50">
		<input type="submit" class="inputButton" value="Submit">
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
