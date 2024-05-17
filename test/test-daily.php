<?
	set_time_limit(0);
	$debug = isset($_GET[debug]);
	if ($debug) header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require_once('/usr/share/pear/Mail.php');

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

 	$today = date('m/d/Y');
	
### HDTV Magazine Daily
	mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Sending HDTV Magazine Daily', ". LOG_TYPE_START .")");

	# Do not email if "get_contents" failed 
	$attempt = 0;
	$message = '';
	while (strlen($message) <= 1 && $attempt < 3) {
		$attempt++;
		if ($attempt > 1) sleep(10); # Delay successive fetches
		$message = file_get_contents('http://www.hdtvmagazine.com/daily.php?emailed');
		mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Attempt $attempt', ". LOG_TYPE_NOTICE .")");
	}

	if ($attempt < 3) {
		$subject = 'HDTV Magazine Daily - '. $today;
		$sql = "
		SELECT id, email_address, email_preference
		FROM user u, phpbb_users pu
		WHERE u.bb_id = pu.user_id
			AND pu.user_active = 1
			AND email_invalid < 3
			AND email_spam = 0
			AND subscriptions & ". SUB_TEST;
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$to = '<'. $row[email_address] .'>';
			$unsub_code = $row[id] .':'. SUB_DAILY;
			mail_send($to, $subject, $message, 'text/html', $unsub_code);
		}
	}
	mQuery("INSERT INTO log VALUES (NOW(), '". PHP_SELF ."', 'Done (Sending HDTV Magazine Daily)', ". LOG_TYPE_END .")");
?>
