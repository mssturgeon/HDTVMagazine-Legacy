<?
	require('global.php');

	$act_key = isset($_GET['act_key']) ? $_GET['act_key'] : '';
	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

	$sql = "
	SELECT user_inactive_reason, user_actkey, user_email, time_zone_offset, time_zone_text, subscriptions
	FROM ". USERS_TABLE ." pu, user u
	WHERE pu.user_id = u.bb_id AND user_id = '$user_id'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (mysql_num_rows($result) == 0 || $act_key == '') {
		js_redirect();
		exit;
	} elseif ($row['user_inactive_reason'] == 0) {
		js_redirect('/login.php', 'Account already activated!');
		exit;
	} elseif ($row['user_actkey'] == $act_key) { // Activate
		$sql = "
		UPDATE ". USERS_TABLE ."
		SET user_type = ". USER_NORMAL .", user_actkey = '', user_inactive_reason = 0, user_inactive_time = 0
		WHERE user_id = '$user_id'";
		mQuery($sql);
	} else {
		js_redirect('/', "Invalid activation key ($act_key)");
	}

	# compute unsub codes. If they are not subscribed (i.e. premium), then don't send the unsub code ... which lets the user know it is a one-time email
	$unsub_code = ($row['subscriptions'] & SUB_TODAY) ? $user_id .':'. SUB_TODAY : '';

	# Send starter emails
	$today = gmdate('m/d/Y');
	$tzo = $row['time_zone_offset'];

	# Send HDTV Today
	$message = file_get_contents(BASE_URL ."/daily.php?user_id=$user_id&tzo=$tzo&d=$today");
	$sql = "
	INSERT INTO email_queue
		(send_to, subject, message, content_type, unsub_code)
		VALUES ('$row[user_email]', 'HDTV Today - $today', '". addslashes($message) ."', 'text/html', '$unsub_code')";
	mQuery($sql);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - User Activation</title>
	<meta http-equiv="refresh" content="3;/login.php">
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>User Activation</h1>
	<p>
		Thank you for activating your account. If you are not redirected to the login page in 3 seconds, please
		<a href="/login.php">click here</a>.
	</p>

	<? include(BASE_DIR .'/includes/body_footer-4.php'); ?>
</body>
</html>
