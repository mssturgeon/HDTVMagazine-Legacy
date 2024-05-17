<?
	set_time_limit(0);
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	# Include necessary libraries for automated scripts
	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');

	# create a boundary string. It must be unique so we use the MD5 algorithm to generate a random hash
	$boundary = "PHP-alt-". md5(date('r', time()));
	$subscription = SUB_FORUM_UPDATE;
//	$subscription = SUB_TEST;

	# Do not email if "get_contents" failed
	$attempt = 0;
	$orig_message = '';
	while (strlen($orig_message) <= 1 && $attempt < 3) {
		$attempt++;
		if ($attempt > 1) sleep(10); # Delay successive fetches
		$orig_message = file_get_contents("http://www.hdtvmagazine.com/admin/emails/forum-update-multipart.php?emailed&boundary=". $boundary);
	}

	if ($attempt < 3) {
		$subject = 'Daily Forum Update - '. date('m/d/Y');
		$sql = "
		SELECT id, first_name, last_name, user_name, email_address
		FROM user
		WHERE email_address <> ''
			AND email_invalid < 3
			AND email_spam = 0
			AND subscriptions & ". $subscription;
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			# Replace unsub_code and email_address
			$message = str_replace('[[email_address]]', $row_email['email_address'], $orig_message);
			$message = str_replace('[[unsub_code]]', urlencode(base64_encode($row_email['id'] .':'. $subscription)), $message);
			$message = str_replace('[[list_name]]', $SUB[$subscription], $message);

			$name = $row['first_name'] .' '. $row['last_name'];
			if (trim($name) == '') {
				$to = $row['user_name'] .' <'. $row['email_address'] .'>';
			} else {
				$to = $name .' <'. $row['email_address'] .'>';
			}

			$sql = "INSERT INTO email_queue (send_to, subject, message, content_type)
			VALUES ('". addslashes($to) ."', '". addslashes($subject) ."', '". addslashes($message) .")', 'multipart/alternative; boundary=\"$boundary\"')";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	} else {
		echo date('Y/m/d H:i:s') .": FAILED - Sending Daily Forum Update\n";
	}
?>